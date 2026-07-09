import React, {FC, memo} from 'react'
import {stockNews} from "@/app/api/stock";

const StockNewsPanel: FC = () => {
    const loadStockNews = async () => {
        try {
            const response = await stockNews('');
        } finally {

        }
    }

    return (
        <div>StockNewsPanel</div>
    )
}

export default memo(StockNewsPanel);
