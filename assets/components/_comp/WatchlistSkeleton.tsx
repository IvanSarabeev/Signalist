import React from "react";

// Base shimmer block. Swap the two bg colors below to match your exact
// theme tokens if they differ from the ones used here.
function Shimmer({ className = "", style = {} }) {
    return (
        <div
            className={`skeleton-shimmer ${className}`}
            style={{
                borderRadius: 4,
                background:
                    "linear-gradient(90deg, #23262f 25%, #2c303b 37%, #23262f 63%)",
                backgroundSize: "400% 100%",
                animation: "skeleton-shimmer 1.4s ease infinite",
                ...style,
            }}
        />
    );
}

function WatchlistRowSkeleton() {
    return (
        <tr style={{ borderTop: "1px solid #2a2d36" }}>
            <td style={{ padding: "14px 16px" }}>
                <Shimmer style={{ width: 16, height: 16, borderRadius: "50%" }} />
            </td>
            <td style={{ padding: "14px 16px" }}>
                <Shimmer style={{ width: 120, height: 14 }} />
            </td>
            <td style={{ padding: "14px 16px" }}>
                <Shimmer style={{ width: 48, height: 20, borderRadius: 4 }} />
            </td>
            <td style={{ padding: "14px 16px" }}>
                <Shimmer style={{ width: 56, height: 14 }} />
            </td>
            <td style={{ padding: "14px 16px" }}>
                <Shimmer style={{ width: 64, height: 14 }} />
            </td>
            <td style={{ padding: "14px 16px" }}>
                <Shimmer style={{ width: 56, height: 14 }} />
            </td>
            <td style={{ padding: "14px 16px" }}>
                <Shimmer style={{ width: 40, height: 14 }} />
            </td>
            <td style={{ padding: "14px 16px" }}>
                <div style={{ display: "flex", gap: 8 }}>
                    <Shimmer style={{ width: 84, height: 32, borderRadius: 6 }} />
                    <Shimmer style={{ width: 96, height: 32, borderRadius: 6 }} />
                </div>
            </td>
        </tr>
    );
}

function AlertCardSkeleton() {
    return (
        <div
            style={{
                border: "1px solid #2a2d36",
                borderRadius: 10,
                padding: 16,
                marginBottom: 16,
            }}
        >
            <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                <Shimmer style={{ width: 36, height: 36, borderRadius: "50%" }} />
                <div style={{ flex: 1 }}>
                    <Shimmer style={{ width: "60%", height: 14, marginBottom: 8 }} />
                    <Shimmer style={{ width: "40%", height: 12 }} />
                </div>
                <Shimmer style={{ width: 50, height: 12 }} />
            </div>

            <div
                style={{
                    marginTop: 16,
                    paddingTop: 12,
                    borderTop: "1px solid #2a2d36",
                }}
            >
                <Shimmer style={{ width: 40, height: 11, marginBottom: 8 }} />
                <div
                    style={{
                        display: "flex",
                        justifyContent: "space-between",
                        alignItems: "center",
                    }}
                >
                    <Shimmer style={{ width: 130, height: 14 }} />
                    <Shimmer style={{ width: 90, height: 20, borderRadius: 10 }} />
                </div>
            </div>
        </div>
    );
}

export default function WatchlistSkeleton({ rows = 4, alerts = 3 }) {
    return (
        <div
            style={{
                color: "#e5e5e5",
                padding: 32,
                minHeight: "100vh",
                fontFamily: "sans-serif",
            }}
        >
            <style>{`
        @keyframes skeleton-shimmer {
          0% { background-position: 100% 50%; }
          100% { background-position: 0 50%; }
        }
      `}</style>

            <div
                style={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    marginBottom: 24,
                }}
            >
                <Shimmer style={{ width: 140, height: 24 }} />
                <div style={{ display: "flex", gap: 12 }}>
                    <Shimmer style={{ width: 100, height: 36, borderRadius: 6 }} />
                    <Shimmer style={{ width: 90, height: 24, marginLeft: 16 }} />
                    <Shimmer style={{ width: 110, height: 36, borderRadius: 6 }} />
                </div>
            </div>

            <div style={{ display: "flex", gap: 24 }}>
                {/* Watchlist table skeleton */}
                <div
                    style={{
                        flex: 1,
                        border: "1px solid #2a2d36",
                        borderRadius: 10,
                        overflow: "hidden",
                    }}
                >
                    <table style={{ width: "100%", borderCollapse: "collapse" }}>
                        <thead>
                            <tr style={{ background: "#1b1e26" }}>
                                {["", "Company", "Symbol", "Price", "Change", "Market cap", "P/E", "Action"].map(
                                    (h, i) => (
                                        <th key={i} style={{ padding: "12px 16px", textAlign: "left" }}>
                                            {h ? <Shimmer style={{ width: 50, height: 12 }} /> : null}
                                        </th>
                                    )
                                )}
                            </tr>
                        </thead>
                        <tbody>
                            {Array.from({ length: rows }).map((_, i) => (
                                <WatchlistRowSkeleton key={i} />
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Alerts panel skeleton */}
                <div style={{ width: 340 }}>
                    {Array.from({ length: alerts }).map((_, i) => (
                        <AlertCardSkeleton key={i} />
                    ))}
                </div>
            </div>
        </div>
    );
}
