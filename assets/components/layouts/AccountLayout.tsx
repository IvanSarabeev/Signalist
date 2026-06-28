import React, {FC, Fragment, memo} from 'react'
import {Toaster} from "sonner";
import Header from "@/components/Header";
import {Outlet} from "react-router-dom";
import {TooltipProvider} from "@/components/ui/tooltip";

const AccountLayout: FC = () => {
    return (
        <Fragment>
            <Toaster/>

            <TooltipProvider>
                <main className="min-h-screen text-gray-400">
                    <Header />

                    <div className="container py-10">
                        <Outlet />
                    </div>
                </main>
            </TooltipProvider>
        </Fragment>
    )
}
export default memo(AccountLayout)
