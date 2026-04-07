@props(['paginator', 'segment' => null])

@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $total = $paginator->total();
    $perPage = $paginator->perPage();
    $firstItem = $paginator->firstItem();
    $lastItem = $paginator->lastItem();
    
    // Calculate range of pages to show
    $range = 2;
    $startPage = max(1, $currentPage - $range);
    $endPage = min($lastPage, $currentPage + $range);
@endphp

<div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
    <!-- Mobile view -->
    <div class="flex-1 flex justify-between sm:hidden">
        @if($paginator->onFirstPage())
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                Previous
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}{{ $segment ? '#'.$segment : '' }}" 
               class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Previous
            </a>
        @endif
        
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}{{ $segment ? '#'.$segment : '' }}" 
               class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Next
            </a>
        @else
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                Next
            </span>
        @endif
    </div>

    <!-- Desktop view -->
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Menampilkan
                <span class="font-medium">{{ $firstItem }}</span>
                –
                <span class="font-medium">{{ $lastItem }}</span>
                dari
                <span class="font-medium">{{ $total }}</span>
                data
            </p>
        </div>
        
        <div>
            <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
                {{-- Previous Page Link --}}
                @if($paginator->onFirstPage())
                    <span class="relative inline-flex items-center px-3 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-400 cursor-not-allowed">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}{{ $segment ? '#'.$segment : '' }}" 
                       class="relative inline-flex items-center px-3 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                {{-- Page Numbers --}}
                @for($i = $startPage; $i <= $endPage; $i++)
                    @if($i == $currentPage)
                        <span aria-current="page" 
                              class="z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            {{ $i }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($i) }}{{ $segment ? '#'.$segment : '' }}" 
                           class="bg-white border-gray-300 text-gray-700 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            {{ $i }}
                        </a>
                    @endif
                @endfor

                {{-- Next Page Link --}}
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}{{ $segment ? '#'.$segment : '' }}" 
                       class="relative inline-flex items-center px-3 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span class="relative inline-flex items-center px-3 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-400 cursor-not-allowed">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </nav>
        </div>
    </div>
</div>
