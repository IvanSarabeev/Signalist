import React, {FC, memo} from 'react'
import {Link} from "react-router-dom";
import LogoImage from "../icons/logo.svg";
import NavItems from "@/components/NavItems";
import UserDropdown from "@/components/UserDropdown";
import {useInitialStocks} from "@/hooks/useInitialStocks";
import {useAuth} from "@/hooks/useAuth";

const Header: FC = () => {
    const {isAuthenticated, user} = useAuth();
    const {initialStocks} = useInitialStocks();

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
                    <NavItems initialStocks={initialStocks}/>
                </nav>

                {(isAuthenticated && user) && (
                    <UserDropdown user={user} initialStocks={initialStocks} />
                )}
            </div>
        </header>
    )
}

export default memo(Header);
