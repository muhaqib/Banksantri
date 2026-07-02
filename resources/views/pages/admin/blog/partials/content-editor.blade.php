@php
    $contentValue = $content ?? '';
    $decodedContent = json_decode($contentValue, true);

    if (
        is_array($decodedContent)
        && ($decodedContent['type'] ?? null) === 'blocks'
        && is_array($decodedContent['blocks'] ?? null)
    ) {
        $contentBlocks = collect($decodedContent['blocks'])
            ->map(fn ($block) => [
                'type' => in_array($block['type'] ?? 'p', ['h2', 'h3', 'p', 'quote'], true) ? $block['type'] : 'p',
                'text' => trim((string) ($block['text'] ?? '')),
            ])
            ->filter(fn ($block) => $block['text'] !== '')
            ->values()
            ->all();
    } else {
        $contentBlocks = collect(preg_split("/\R{2,}/", trim($contentValue)) ?: [])
            ->map(fn ($paragraph) => ['type' => 'p', 'text' => trim($paragraph)])
            ->filter(fn ($block) => $block['text'] !== '')
            ->values()
            ->all();
    }

    if (empty($contentBlocks)) {
        $contentBlocks = [
            ['type' => 'p', 'text' => ''],
        ];
    }
@endphp

<div x-data='blogContentEditor(@json($contentBlocks))' class="blog-content-editor">
    <input type="hidden" name="content" x-ref="contentInput" x-bind:value="serializedContent" required>

    <div class="space-y-3">
        <template x-for="(block, index) in blocks" :key="block.id">
            <div class="rounded-xl bg-surface-container-high p-3 transition-all focus-within:bg-surface-container-highest">
                <div class="flex items-center gap-2 mb-3">
                    <select x-model="block.type"
                            class="h-10 rounded-lg border-none bg-surface-container-lowest px-3 text-xs font-bold uppercase text-primary focus:ring-0">
                        <option value="h2">H2</option>
                        <option value="h3">H3</option>
                        <option value="p">P</option>
                        <option value="quote">Kutipan</option>
                    </select>

                    <button type="button"
                            @click="addBlock(index + 1)"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-on-primary transition-colors hover:bg-primary-container"
                            title="Tambah blok">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                    </button>

                    <button type="button"
                            @click="removeBlock(index)"
                            x-show="blocks.length > 1"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-error-container text-on-error-container transition-colors hover:opacity-85"
                            title="Hapus blok">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>

                <textarea x-model="block.text"
                          rows="3"
                          :placeholder="placeholderFor(block.type)"
                          class="w-full resize-y border-none bg-transparent p-0 text-on-surface transition-all focus:ring-0"
                          :class="{
                              'font-headline text-2xl font-extrabold leading-tight': block.type === 'h2',
                              'font-headline text-xl font-bold leading-tight': block.type === 'h3',
                              'text-base leading-relaxed': block.type === 'p',
                              'border-l-4 border-primary/40 pl-4 italic leading-relaxed text-on-surface-variant': block.type === 'quote'
                          }"></textarea>
            </div>
        </template>
    </div>

    <button type="button"
            @click="addBlock(blocks.length)"
            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary/10 px-4 py-3 text-sm font-bold text-primary transition-colors hover:bg-primary/15">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        <span>Tambah Blok Konten</span>
    </button>
</div>

@once
    @push('scripts')
        <script>
        function blogContentEditor(initialBlocks) {
            const createBlockId = () => (
                window.crypto && window.crypto.randomUUID
                    ? window.crypto.randomUUID()
                    : `${Date.now()}-${Math.random()}`
            );

            return {
                blocks: (initialBlocks.length ? initialBlocks : [{ type: 'p', text: '' }]).map((block) => ({
                    id: createBlockId(),
                    type: block.type || 'p',
                    text: block.text || '',
                })),
                get serializedContent() {
                    return JSON.stringify({
                        type: 'blocks',
                        blocks: this.blocks
                            .map((block) => ({
                                type: ['h2', 'h3', 'p', 'quote'].includes(block.type) ? block.type : 'p',
                                text: block.text.trim(),
                            }))
                            .filter((block) => block.text !== ''),
                    });
                },
                addBlock(position) {
                    this.blocks.splice(position, 0, {
                        id: createBlockId(),
                        type: 'p',
                        text: '',
                    });

                    this.$nextTick(() => {
                        const textareas = this.$el.querySelectorAll('textarea');
                        textareas[position]?.focus();
                    });
                },
                removeBlock(index) {
                    if (this.blocks.length === 1) {
                        return;
                    }

                    this.blocks.splice(index, 1);
                },
                placeholderFor(type) {
                    return {
                        h2: 'Tulis subjudul utama...',
                        h3: 'Tulis subjudul kecil...',
                        p: 'Tulis paragraf...',
                        quote: 'Tulis kutipan...',
                    }[type] || 'Tulis konten...';
                },
            };
        }
        </script>
    @endpush
@endonce
