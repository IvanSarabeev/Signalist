import api from "@/lib/axiosApi";

export async function stocksSearch(symbol: string = ''): Promise<StocksResponse> {
    return api.get(`/stocks?symbol=${encodeURIComponent(symbol)}`);
}

export async function stockNews(symbol: string) {
    return api.get(`/stocks/${symbol}/company-news`);
}

type MarketNewsCategory =
    | "general"
    | "forex"
    | "crypto"
    | "merger";

export async function getMarketNews(page: number, limit: number, category: MarketNewsCategory): Promise<MarketNewsResponse> {
    const urlParams = new URLSearchParams();

    if (page > 0) {
        urlParams.set('page', String(page));
    }

    if (limit > 0) {
        urlParams.set('limit', String(limit));
    }

    return api.get(`/stocks/${category}/news?${urlParams.toString()}`);
}
