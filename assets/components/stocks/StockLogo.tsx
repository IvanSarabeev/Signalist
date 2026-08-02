import React, {FC, memo} from 'react'

type StockLogoProps = {symbol: string; size: number;}

const StockLogo: FC<StockLogoProps> = ({symbol, size = 36}) => {
    const LOGO_COLORS = {
        AAPL: { bg: "#1c1c1e", text: "#fff", letter: "A" },
        MSFT: { bg: "#0078d4", text: "#fff", letter: "M" },
        GOOGL: { bg: "#4285F4", text: "#fff", letter: "G" },
        AMZN: { bg: "#FF9900", text: "#fff", letter: "A" },
        TSLA: { bg: "#CC0000", text: "#fff", letter: "T" },
        META: { bg: "#0081FB", text: "#fff", letter: "M" },
        NVDA: { bg: "#76B900", text: "#fff", letter: "N" },
        NFLX: { bg: "#E50914", text: "#fff", letter: "N" },
        ORCL: { bg: "#F80000", text: "#fff", letter: "O" },
        CRM: { bg: "#00A1E0", text: "#fff", letter: "S" },
        INTC: { bg: "#0071C5", text: "#fff", letter: "I" },
        JNJ: { bg: "#D62117", text: "#fff", letter: "J" },
    };

    const cfg = LOGO_COLORS[symbol] || { bg: "#374151", text: "#fff", letter: symbol[0] };

    return (
        <div
            style={{
                width: size,
                height: size,
                borderRadius: 8,
                background: cfg.bg,
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                flexShrink: 0,
                fontSize: size * 0.44,
                fontWeight: 700,
                color: cfg.text,
                userSelect: "none",
            }}
        >
            {cfg.letter}
        </div>
    )
}

export default memo(StockLogo);
