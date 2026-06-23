<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class WahaService
{
    public function getConnectionStatus(): array
    {
        try {
            $sessions = $this->http()->get($this->path('/sessions'))->throw()->json();
            $session = $this->findDefaultSession($sessions);
            $status = strtoupper((string) data_get($session, 'status', data_get($session, 'state', 'DISCONNECTED')));
            $isConnected = in_array($status, ['CONNECTED', 'WORKING'], true);

            return [
                'connected' => $isConnected,
                'status' => $isConnected ? 'CONNECTED' : ($status ?: 'DISCONNECTED'),
                'device' => data_get($session, 'me.pushName')
                    ?? data_get($session, 'me.user')
                    ?? data_get($session, 'name')
                    ?? config('services.waha.session', 'default'),
                'qr' => $this->extractQr($session),
                'error' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'connected' => false,
                'status' => 'DISCONNECTED',
                'device' => null,
                'qr' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function getWahaGroups(): array
    {
        try {
            $groups = $this->http()->get($this->path('/'.config('services.waha.session', 'WAHA').'/chats'), [
                'chatsType' => 'GROUPS',
            ])->throw()->json();

            $groups = data_get($groups, 'data', $groups);

            return collect(is_array($groups) ? $groups : [])
                ->filter(fn (mixed $group): bool => is_array($group))
                ->map(function (array $group): array {
                    $id = data_get($group, 'id._serialized')
                        ?? data_get($group, 'id')
                        ?? data_get($group, 'chatId');

                    $id = is_string($id) ? $id : null;

                    return [
                        'id' => $id,
                        'name' => data_get($group, 'name')
                            ?? data_get($group, 'title')
                            ?? data_get($group, 'pushname')
                            ?? $id,
                    ];
                })
                ->filter(fn (array $group): bool => filled($group['id']) && Str::endsWith($group['id'], '@g.us'))
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    public function sendMessage(string $chatId, string $message): bool
    {
        try {
            $this->http()->post($this->path('/sendText'), [
                'session' => config('services.waha.session', 'WAHA'),
                'chatId' => $chatId,
                'text' => $message,
            ])->throw();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.waha.base_url'), '/'))
            ->timeout((int) config('services.waha.timeout', 15))
            ->withHeaders(array_filter([
                'X-Api-Key' => config('services.waha.api_key'),
            ]))
            ->acceptJson()
            ->asJson();
    }

    private function path(string $path): string
    {
        $baseUrl = rtrim((string) config('services.waha.base_url'), '/');

        return Str::endsWith($baseUrl, '/api') ? $path : '/api'.$path;
    }

    private function findDefaultSession(mixed $sessions): array
    {
        $sessionName = config('services.waha.session', 'default');
        $sessions = data_get($sessions, 'sessions', $sessions);

        if (is_array($sessions) && array_is_list($sessions)) {
            $sessions = collect($sessions)->filter(fn (mixed $session): bool => is_array($session));
        } elseif (is_array($sessions)) {
            $sessions = collect([$sessions]);
        } else {
            $sessions = collect();
        }

        return $sessions->first(fn (array $session): bool => data_get($session, 'name') === $sessionName)
            ?? $sessions->first()
            ?? [];
    }

    private function extractQr(array $session): ?string
    {
        $qr = data_get($session, 'qr')
            ?? data_get($session, 'qrCode')
            ?? data_get($session, 'metadata.qr')
            ?? data_get($session, 'config.qr');

        if (! is_string($qr) || $qr === '') {
            return null;
        }

        if (Str::startsWith($qr, ['data:image', 'http://', 'https://'])) {
            return $qr;
        }

        return 'data:image/png;base64,'.$qr;
    }
}
