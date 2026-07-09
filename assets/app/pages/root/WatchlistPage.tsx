import React, {FC, memo, Suspense, useState} from "react";
import {getWatchlist} from "@/app/api/watchlist";
import {getAlerts} from "@/app/api/alerts";
import WatchlistSkeleton from "@/components/_comp/WatchlistSkeleton";
import WatchlistContent from "@/components/WatchlistContent";

export type WatchlistsResource = { data: StockWithData[]; meta: MetaResponse | [] };
export type AlertsResource = { data: Alert[]; meta: MetaResponse | [] };

async function fetchWatchlistItems(): Promise<WatchlistsResource> {
    try {
        const result = await getWatchlist();
        return {
            data: result.status && result.data.length > 0 ? result.data : [],
            meta: result.meta ?? [],
        };
    } catch {
        return { data: [] as StockWithData[], meta: [] };
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
        return { data: [] as Alert[], meta: [] };
    }
}

const WatchlistPage: FC = () => {
    const [watchlistsPromise] = useState(fetchWatchlistItems);
    const [alertsPromise] = useState(fetchAlerts);

    return (
        <Suspense fallback={<WatchlistSkeleton rows={4} alerts={3} />}>
            <WatchlistContent watchlistsPromise={watchlistsPromise} alertsPromise={alertsPromise} />
        </Suspense>
    );
};

export default memo(WatchlistPage);
