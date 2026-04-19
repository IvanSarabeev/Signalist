import React, {FC, memo, useMemo} from 'react'
import TradingViewWidget from "@/components/TradingViewWidget";
import {
    ADVANCED_TRADING_VIEW_WIDGET, BASELINE_WIDGET_CONFIG,
    CANDLE_CHART_WIDGET_CONFIG,
    COMPANY_FINANCIALS_WIDGET_CONFIG,
    COMPANY_PROFILE_WIDGET_CONFIG,
    SYMBOL_INFO_WIDGET_CONFIG,
    TECHNICAL_ANALYSIS_WIDGET_CONFIG
} from "@/lib/constants";
import {useParams} from "react-router";

const StockDetailPage: FC = () => {
    const {symbol} = useParams<{symbol: string}>();

    const widgetConfigs = useMemo(() => ({
        symbolInfo: SYMBOL_INFO_WIDGET_CONFIG(symbol ?? ''),
        candleChart: CANDLE_CHART_WIDGET_CONFIG(symbol ?? ''),
        baselineChart: BASELINE_WIDGET_CONFIG(symbol ?? ''),
        tech: TECHNICAL_ANALYSIS_WIDGET_CONFIG(symbol ?? ''),
        profile: COMPANY_PROFILE_WIDGET_CONFIG(symbol ?? ''),
        financials: COMPANY_FINANCIALS_WIDGET_CONFIG(symbol ?? ''),
    }), [symbol]);

    if (!symbol) {
        return <div>No symbol found.</div>
    }

    return (
        <div className="flex min-h-screen p-4 md:p-6 lg:p-8">
            <section className="grid grid-cols-1 md:grid-cols-2 gap-8 w-full">
                <div className="flex flex-col gap-6">
                    <TradingViewWidget
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}symbol-info.js`}
                        config={widgetConfigs.symbolInfo}
                        height={170}
                    />

                    <TradingViewWidget
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}advanced-chart.js`}
                        config={widgetConfigs.candleChart}
                        className="custom-chart"
                        height={600}
                    />

                    <TradingViewWidget
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}advanced-chart.js`}
                        config={widgetConfigs.baselineChart}
                        className="custom-chart"
                        height={600}
                    />
                </div>

                <div className="flex flex-col gap-6">
                    <div className="flex items-center justify-between">
                        Watchlist Button
                    </div>

                    <TradingViewWidget
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}technical-analysis.js`}
                        config={widgetConfigs.tech}
                        height={400}
                    />

                    <TradingViewWidget
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}symbol-profile.js`}
                        config={widgetConfigs.profile}
                        height={440}
                    />

                    <TradingViewWidget
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}financials.js`}
                        config={widgetConfigs.financials}
                        height={464}
                    />
                </div>
            </section>
        </div>
    )
}

export default memo(StockDetailPage);
