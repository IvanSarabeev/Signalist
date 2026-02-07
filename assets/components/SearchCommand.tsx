import React, {FC, useState} from 'react'

const SearchCommand: FC<SearchCommandProps> = ({renderAs = "button", label = "Add stock", initialStocks}) => {
    const [searchData, setSearchData] = useState({
        open: false,
        searchTerm: '',
        loading: false,
        stocks: initialStocks
    });


    return (
        <div>SearchCommand</div>
    )
}
export default SearchCommand
