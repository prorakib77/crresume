@props(['paginator'])

@if($paginator->hasPages())
    <div class="beautiful-pagination">
        <div class="pagination-info">
            <div class="info-card">
                <i class="fas fa-info-circle"></i>
                <span class="info-text">
                    Showing <strong>{{ $paginator->firstItem() ?? 0 }}</strong> to
                    <strong>{{ $paginator->lastItem() ?? 0 }}</strong> of
                    <strong>{{ $paginator->total() }}</strong> results
                </span>
            </div>
        </div>

        <div class="pagination-controls">
            @if($paginator->onFirstPage())
                <button class="pagination-btn disabled" disabled>
                    <i class="fas fa-chevron-left"></i>
                    <span>Previous</span>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn prev-btn">
                    <i class="fas fa-chevron-left"></i>
                    <span>Previous</span>
                </a>
            @endif

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn next-btn">
                    <span>Next</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <button class="pagination-btn disabled" disabled>
                    <span>Next</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
            @endif
        </div>
    </div>
@endif

<style>
.beautiful-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 1.5rem 0;
    padding: 1rem 1.5rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}


.pagination-info {
    flex: 1;
}

.info-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: transparent;
    padding: 0.5rem 0;
    border: none;
    box-shadow: none;
}


.info-card i {
    font-size: 1rem;
    color: #6b7280;
}

.info-text {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    line-height: 1.4;
}

.info-text strong {
    color: #000000;
    font-weight: 600;
}

.pagination-controls {
    display: flex;
    gap: 0.5rem;
}

.pagination-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #ffffff;
    color: #000000;
    text-decoration: none;
    border-radius: 0.375rem;
    font-weight: 500;
    font-size: 0.875rem;
    border: 1px solid #d1d5db;
    transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}


.pagination-btn:hover {
    background: #ffffff;
    color: #000000;
    border-color: #d1d5db;
    transform: scale(1.02);
}

.pagination-btn:active {
    transform: scale(0.98);
}

.pagination-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f9fafb;
    color: #9ca3af;
    border-color: #e5e7eb;
}

.pagination-btn.disabled:hover {
    background: #f9fafb;
    border-color: #e5e7eb;
    color: #9ca3af;
}

.pagination-btn i {
    font-size: 0.875rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .beautiful-pagination {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }

    .pagination-controls {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .beautiful-pagination {
        padding: 0.75rem;
    }
}

/* Focus states for accessibility */
.pagination-btn:focus {
    outline: 2px solid #000000;
    outline-offset: 2px;
}
</style>
