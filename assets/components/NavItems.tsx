import React, {FC} from 'react'
import {useLocation} from "react-router";
import {NAV_ITEMS} from "@/lib/constants";
import SearchCommand from "@/components/SearchCommand";
import {Link} from "react-router-dom";

const NavItems: FC<{ initialStocks: StockWithWatchlistStatus[] }> = ({initialStocks}) => {
    const locationPathname = useLocation();

    const isActive = (path: string) => {
        if (path === '/account') return locationPathname.pathname === '/account';

        return locationPathname.pathname.startsWith(path);
    };

    return (
        <ul className="flex flex-col sm:flex-row p2 gap-3 sm:gap-10 font-medium">
            {NAV_ITEMS.map(({href, label}) => {
                if (href === '/account/search') return (
                    <li key='search-trigger'>
                        <SearchCommand
                            renderAs="text"
                            label="Search"
                            initialStocks={initialStocks}
                        />
                    </li>
                )

                return (
                    <li key={href}>
                        <Link to={href} className={`hover:text-yellow-500 transition-colors ${isActive(href) && 'text-gray-100'}`}>
                            {label}
                        </Link>
                    </li>
                )
            })}
        </ul>
    )
}
export default NavItems
