import api from "@/lib/axiosApi";

export async function stocksSearch(symbol: string = ''): Promise<FinnhubSearchResponse> {
    return api.get(`/stocks?symbol=${encodeURIComponent(symbol ?? '')}`);
};

export async function stockNews(symbol: string) {
    return api.get(`/stocks/${symbol}/news`);
};
