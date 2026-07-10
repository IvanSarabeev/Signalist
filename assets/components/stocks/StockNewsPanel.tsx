import React, {FC, Fragment, memo, use, useCallback, useState, useTransition} from 'react'
import StockCard from "@/components/stocks/StockCard";
import {MarketNewsResource} from "@/app/pages/root/WatchlistPage";
import Pagination from "@/components/_comp/Pagination";
import {getMarketNews} from "@/app/api/stock";

type StockNewsPanelProps = {
    marketNewsPromise: Promise<MarketNewsResource>;
}

const StockNewsPanel: FC<StockNewsPanelProps> = ({marketNewsPromise}) => {
    const marketNewsResource = use(marketNewsPromise);

    const [marketNews, setMarketNews] = useState(marketNewsResource.data);
    const [pagination, setPagination] = useState({
        page: 1,
        limit: 10,
        total_pages: 0,
        has_next_page: false,
        has_previous_page: false,
        ...marketNewsResource.meta
    });
    const [isPending, startTransition] = useTransition();

    const handlePageChange = useCallback((nextPage: number) => {
        startTransition(async () => {
            const nextResource = await getMarketNews(nextPage, pagination.limit, "general");

            if (nextResource.status && nextResource.data.length > 0) {
                setMarketNews(nextResource.data);
                setPagination((prevState) => ({
                    ...prevState,
                    page: nextPage,
                    ...nextResource.meta,
                }));
            }
        });
    }, [pagination.limit]);

    return (
        <Fragment>
            <section className="max-w-screen-2xl size-full my-4 mx-auto">
                <h3 className="article-title">News</h3>

                <article className="news-article">
                    {marketNews.length > 0
                        ? marketNews.map((news) => {
                            return (
                                <StockCard key={news.url} article={news}/>
                            )
                        })
                        : <p className="empty-description">No news available right now.</p>
                    }
                </article>
            </section>

            <Pagination
                page={pagination.page}
                limit={pagination.limit}
                total_pages={pagination.total_pages}
                has_previous_page={pagination.has_previous_page}
                has_next_page={pagination.has_next_page}
                onPageChange={handlePageChange}
                disabled={isPending}
            />
        </Fragment>
    )
}

export default memo(StockNewsPanel);
