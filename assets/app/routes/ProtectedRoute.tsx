import React, {ReactNode} from 'react'
import {useAuth} from "@/hooks/useAuth";
import {Navigate} from "react-router-dom";

type ProtectedRouteType = {
    children: ReactNode
};

const ProtectedRoute = ({children}: ProtectedRouteType) => {
    const {isAuthenticated} = useAuth();

    if (!isAuthenticated) {
        return <Navigate to="/sign-in" replace/>
    }

    return children;
};

export default ProtectedRoute;
