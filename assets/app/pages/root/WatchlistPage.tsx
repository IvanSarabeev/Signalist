import React, {FC, Fragment, memo, Suspense, useState} from "react";
import {getWatchlist} from "@/app/api/watchlist";
import {getAlerts} from "@/app/api/alerts";
import WatchlistSkeleton from "@/components/_comp/WatchlistSkeleton";
import WatchlistContent from "@/components/WatchlistContent";
import StockNewsSkeleton from "@/components/stocks/StockNewsSkeleton";
import {getMarketNews} from "@/app/api/stock";
import StockNewsPanel from "@/components/stocks/StockNewsPanel";

export type WatchlistsResource = { data: StockWithData[]; meta: MetaResponse };
export type AlertsResource = { data: Alert[]; meta: MetaResponse };
export type MarketNewsResource = { data: MarketNewsArticle[]; meta: MetaResponse };

async function fetchWatchlistItems(): Promise<WatchlistsResource> {
    try {
        const result = await getWatchlist();
        return {
            data: result.status && result.data.length > 0 ? result.data : [],
            meta: result.meta ?? {},
        };
    } catch {
        return { data: [] as StockWithData[], meta: {} };
    }
}

async function fetchAlerts(): Promise<AlertsResource> {
    try {
        const result = await getAlerts();
        return {
            data: result.status && result.data.length > 0 ? result.data : [],
            meta: result.meta ?? {}
        };
    } catch {
        return { data: [] as Alert[], meta: {} };
    }
}

async function fetchMarketNews(): Promise<MarketNewsResource> {
    try {
        const result = await getMarketNews(1, 10, 'general');

        return {
            data: result.status && result.data.length > 0 ? result.data : [],
            meta: result.meta ?? {},
        }
    } catch {
        return { data: [] as MarketNewsArticle[], meta: {} };
    }
}

const WatchlistPage: FC = () => {
    const [watchlistsPromise] = useState(fetchWatchlistItems);
    const [alertsPromise]     = useState(fetchAlerts);
    const [marketNewsPromise] = useState(fetchMarketNews);

    return (
        <Fragment>
            <Suspense fallback={<WatchlistSkeleton rows={4} alerts={3} />}>
                <WatchlistContent watchlistsPromise={watchlistsPromise} alertsPromise={alertsPromise} />
            </Suspense>

            <Suspense fallback={<StockNewsSkeleton />}>
                <StockNewsPanel marketNewsPromise={marketNewsPromise} />
            </Suspense>
        </Fragment>
    );
};

export default memo(WatchlistPage);
