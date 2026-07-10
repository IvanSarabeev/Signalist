import React, {FC, memo} from 'react'
import {Link} from "react-router-dom";

type StockCardProps = {
    article: MarketNewsArticle
};

const StockCard: FC<StockCardProps> = ({article}) => {
    const {source, summary, headline, datetime, url} = article;

    return (
        <Link
            to={url}
            target="_blank"
            rel="noopener noreferrer"
            className="news-item"
        >
            <span className="news-tag">{source}</span>

            <h3 className="news-title">{headline}</h3>

            <div className="news-meta">
                <span>{source}</span>

                {" "}
                <span className="mx-2-size-1 rounded-full bg-gray-500" />

                <span>{datetime}</span>
            </div>

            <p className="news-summary">{summary}</p>

            <span className="news-cta">Read More &rarr;</span>
        </Link>
    )
}

export default memo(StockCard);
