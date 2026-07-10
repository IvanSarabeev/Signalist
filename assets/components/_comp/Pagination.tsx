import React, {FC, memo, useMemo} from 'react'
import {Button} from "@/components/ui/button";
import {ChevronLeft, ChevronRight} from "lucide-react";

type PaginationProps = {
    page: number;
    limit: number;
    has_next_page: boolean;
    has_previous_page: boolean;
    total_pages?: number;
    onPageChange: (page: number) => void;
    siblingCount?: number;
    disabled?: boolean;
}

const DOTS = 'dots' as const;
type PageItem = number | typeof DOTS;

function getPageRange(page: number, totalPages: number, siblingCount: number): PageItem[] {
    const totalNumbers = siblingCount * 2 + 5;

    if (totalPages <= totalNumbers) {
        return Array.from({lengh: totalPages}, (_, i) => i + 1);
    }

    const leftSibling = Math.max(page - siblingCount, 1);
    const rightSibling = Math.min(page + siblingCount, totalPages);

    const showLeftDots = leftSibling > 2;
    const showRightDots = rightSibling < totalPages - 1;

    if (!showLeftDots && showRightDots) {
        const leftRange = Array.from({length: 3 + siblingCount * 2}, (_, i) => i + 1);
        return [...leftRange, DOTS, totalPages];
    }

    if (showLeftDots && !showRightDots) {
        const rightRange = Array.from(
            {length: 3 + siblingCount * 2},
            (_, i) => totalPages - (3 + siblingCount * 2) + i + 1
        );
        return [1, DOTS, ...rightRange];
    }

    const middleRange = Array.from(
        {length: rightSibling - leftSibling + 1},
        (_, i) => leftSibling + i
    );
    return [1, DOTS, ...middleRange, DOTS, totalPages];
}

const navBtn =
    'flex h-8 w-8 cursor-pointer items-center justify-center rounded border border-gray-600 text-gray-400 transition-colors hover:border-yellow-500 hover:text-yellow-500 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-gray-600 disabled:hover:text-gray-400';

const pageBtn =
    'flex h-8 min-w-8 cursor-pointer items-center justify-center rounded border border-gray-600 px-2 text-sm text-gray-400 transition-colors hover:border-yellow-500 hover:text-yellow-500';

const pageBtnActive =
    'flex h-8 min-w-8 cursor-pointer items-center justify-center rounded bg-yellow-500 px-2 text-sm font-semibold text-gray-900';

const Pagination: FC<PaginationProps> = ({
    page,
    limit,
    has_next_page,
    has_previous_page,
    total_pages,
    onPageChange,
    siblingCount = 1,
    disabled = false,
}) => {
    const totalPages = total_pages ?? 0;

    const pageRange = useMemo(
        () => (totalPages > 0 ? getPageRange(page, totalPages, siblingCount) : []),
        [page, totalPages, siblingCount]
    );

    const handleClick = (target: number) => {
        if (target === page) return;
        if (target < 1) return;
        if (totalPages > 0 && target > totalPages) return;
        onPageChange(target);
    };

    return (
        <nav
            aria-label="pagination"
            className={`flex items-center justify-center gap-1 5 ${disabled ? 'pointer-events-none opacity-50' : 'cursor-pointer'}`}
        >
            <Button
                type="button"
                aria-label="Previous page"
                disabled={!has_previous_page}
                onClick={() => handleClick(page - 1)}
                className={navBtn}
            >
                <ChevronLeft className="size-4"/>
            </Button>

            {pageRange.map((item, idx) =>
                item === DOTS ? (
                    <span key={`dots-${idx}`} className="px-1 text-gray-500">
                        &hellip;
                    </span>
                ) : (
                    <Button
                        key={item}
                        type="button"
                        aria-current={item === page ? 'page' : undefined}
                        onClick={() => handleClick(item)}
                        className={item === page ? pageBtnActive : pageBtn}
                    >
                        {item}
                    </Button>
                )
            )}

            <Button
                type="button"
                aria-label="Next page"
                disabled={!has_next_page}
                onClick={() => handleClick(page + 1)}
                className={navBtn}
            >
                <ChevronRight className="size-4"/>
            </Button>
        </nav>
    )
}

export default memo(Pagination);
