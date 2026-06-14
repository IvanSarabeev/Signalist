import api from "@/lib/axiosApi";

export async function getWatchlist(): Promise<WatchlistResponse> {
    return api.get(`/watchlist`);
}

export async function addWatchlistItem(symbol: string): Promise<AddWatchlistItemResponse> {
    return api.post(`/watchlist/${symbol}`);
}
