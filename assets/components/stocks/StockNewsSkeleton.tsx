import React, { FC, memo, CSSProperties } from 'react'

const shimmerStyle: CSSProperties = {
    background: 'linear-gradient(90deg, #23262f 25%, #2c303b 37%, #23262f 63%)',
    backgroundSize: '400% 100%',
    animation: 'stock-news-shimmer 1.4s ease infinite',
    borderRadius: 4,
}

interface StockNewsCardSkeletonProps {
    descriptionLines?: number
}

const StockNewsCardSkeleton: FC<StockNewsCardSkeletonProps> = ({descriptionLines = 3}) => {
    return (
        <div
            style={{
                background: '#1b1e26',
                border: '1px solid #2a2d36',
                borderRadius: 10,
                padding: 16,
                display: 'flex',
                flexDirection: 'column',
                gap: 10,
            }}
        >
            {/* Ticker badge */}
            <div style={{ ...shimmerStyle, width: 56, height: 20, borderRadius: 4 }} />

            {/* Headline (2 lines) */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 6, marginTop: 4 }}>
                <div style={{ ...shimmerStyle, width: '95%', height: 16 }} />
                <div style={{ ...shimmerStyle, width: '70%', height: 16 }} />
            </div>

            {/* Source + timestamp */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 2 }}>
                <div style={{ ...shimmerStyle, width: 90, height: 12 }} />
                <div style={{ ...shimmerStyle, width: 4, height: 4, borderRadius: '50%' }} />
                <div style={{ ...shimmerStyle, width: 80, height: 12 }} />
            </div>

            {/* Description */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 6, marginTop: 4 }}>
                {Array.from({ length: descriptionLines }).map((_, i) => (
                    <div
                        key={i}
                        style={{
                            ...shimmerStyle,
                            width: i === descriptionLines - 1 ? '55%' : '100%',
                            height: 12,
                        }}
                    />
                ))}
            </div>

            {/* Read more */}
            <div style={{ ...shimmerStyle, width: 70, height: 12, marginTop: 6 }} />
        </div>
    )
}

interface StockNewsSkeletonProps {
    count?: number
    columns?: number
}

const StockNewsSkeleton: FC<StockNewsSkeletonProps> = ({
    count = 8,
    columns = 4
}) => {
    return (
        <div>
            <style>{`
        @keyframes stock-news-shimmer {
          0% { background-position: 100% 50%; }
          100% { background-position: 0 50%; }
        }
      `}</style>
            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
                    gap: 16,
                }}
            >
                {Array.from({ length: count }).map((_, i) => (
                    <StockNewsCardSkeleton key={i} />
                ))}
            </div>
        </div>
    )
}

export default memo(StockNewsSkeleton);
