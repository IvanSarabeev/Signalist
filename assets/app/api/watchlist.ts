import api from "@/lib/axiosApi";

export async function getWatchlist(): Promise<WatchlistResponse> {
    return api.get(`/watchlist`);
}
