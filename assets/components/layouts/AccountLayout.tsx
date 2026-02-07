import React, {FC, Fragment} from 'react'
import {Toaster} from "sonner";
import Header from "@/components/Header";
import {Outlet} from "react-router-dom";

const AccountLayout: FC = () => {
    return (
        <Fragment>
            <Toaster/>

            <main className="min-h-screen text-gray-400">
                <Header />

                <div className="container py-10">
                    <Outlet />
                </div>
            </main>
        </Fragment>
    )
}
export default AccountLayout
