import React, {FC} from 'react'
import {Link} from "react-router-dom";
import LogoImage from "../icons/logo.svg";
import UserDropdown from "@/components/UserDropdown";

const Header: FC = () => {
    return (
        <header className="sticky top-0 header">
            <div className="container header-wrapper">
                <Link to="/account">
                    <img
                        src={LogoImage}
                        alt="Signalist logo"
                        width={140}
                        height={32}
                        className="h-8 w-auto cursor-pointer"
                    />
                </Link>
                <nav className="hidden sm:block">
                    {/*<NavItems initialStocks={initialStocks}/>*/}
                </nav>

                {/*<UserDropdown user={user} initialStocks={initialStocks} />*/}
            </div>
        </header>
    )
}

export default Header
