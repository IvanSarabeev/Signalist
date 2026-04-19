import React, {FC, memo, useMemo} from 'react'
import TradingViewWidget from "@/components/TradingViewWidget";
import {
    ADVANCED_TRADING_VIEW_WIDGET,
    HEATMAP_WIDGET_CONFIG,
    MARKET_DATA_WIDGET_CONFIG,
    MARKET_OVERVIEW_WIDGET_CONFIG,
    TOP_STORIES_WIDGET_CONFIG
} from "@/lib/constants";

const Home: FC = () => {
    const configs = useMemo(() => ({
        overview: MARKET_OVERVIEW_WIDGET_CONFIG,
        heatmap: HEATMAP_WIDGET_CONFIG,
        stories: TOP_STORIES_WIDGET_CONFIG,
        market: MARKET_DATA_WIDGET_CONFIG,
    }), []);

    return (
        <div className="flex min-h-screen home-wrapper">
            <section className="grid w-full gap-8 home-section">
                <div className="md:col-span-1 xl:col-span-1">
                    <TradingViewWidget
                        title="Market Overview"
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}market-overview.js`}
                        config={configs.overview}
                        className="custom-chart"
                        height={600}
                    />
                </div>

                <div className="md:col-span-1 xl:col-span-2">
                    <TradingViewWidget
                        title="Stock Heatmap"
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}stock-heatmap.js`}
                        config={configs.heatmap}
                        height={600}
                    />
                </div>
            </section>

            <section className="grid w-full gap-8 home-section">
                <div className="h-full md:col-span-1 xl:col-span-1">
                    <TradingViewWidget
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}timeline.js`}
                        config={configs.stories}
                        className="custom-chart"
                        height={600}
                    />
                </div>

                <div className="h-full md:col-span-1 xl:col-span-2">
                    <TradingViewWidget
                        scriptUrl={`${ADVANCED_TRADING_VIEW_WIDGET}market-quotes.js`}
                        config={configs.market}
                        height={600}
                    />
                </div>
            </section>
        </div>
    )
}

export default memo(Home);
