import React, { FC } from 'react';
import { Link, Outlet } from "react-router-dom";
import LogoImage from "../../icons/logo.svg";
import StarSvg from "../../icons/star.svg";
import DashboardImage from '../../images/dashboard.png';

const AuthLayout: FC= () => {
    return (
        <main className="auth-layout bg-red-500">
            <section className="auth-left-section scrollbar-hide-default">
                <Link to="/" className="auth-logo">
                    <img
                        src={LogoImage}
                        alt="Signalist logo"
                        width={140}
                        height={32}
                        decoding="async"
                        loading={"lazy"}
                        className="h-8 w-auto"
                    />
                </Link>

                <div className="pb-6 lg:pb-8 flex-1">
                    <Outlet />
                </div>
            </section>

            <section className="auth-right-section">
                <div className="z-10 relative lg:mt-4 lg:mb-16">
                    <blockquote className="auth-blockquote">
                        Signalist turned my watchlist into a winning list. 1234
                        The alerts are spot-on and I feel more confident making moves in the market
                    </blockquote>

                    <div className="flex items-center justify-between">
                        <div>
                            <cite className="auth-testimonial-author-name">- Ethan R.</cite>
                            <p className="max-md:text-xs text-gray-500">Retail Investor</p>
                        </div>

                        <div className="flex items-center gap-0 5">
                            {[1, 2, 3, 4, 5].map((star) => (
                                <img
                                    src={StarSvg}
                                    alt="Star"
                                    key={star}
                                    width={20}
                                    height={20}
                                    className="size-5"
                                    loading="lazy"
                                    decoding="async"
                                />
                            ))}
                        </div>
                    </div>
                </div>

                <div className="flex-1 relative">
                    <img
                        src={DashboardImage}
                        alt="Dashboard Preview"
                        width={1440}
                        height={1150}
                        className="auth-dashboard-preview absolute top-0"
                        loading="lazy"
                        decoding="auto"
                    />
                </div>
            </section>
        </main>
    )
}

export default AuthLayout;
