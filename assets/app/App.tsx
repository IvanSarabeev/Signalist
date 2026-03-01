import React, {FC, ReactNode} from 'react';
import {createBrowserRouter, Navigate, RouterProvider} from "react-router-dom";
import AuthLayout from "../components/layouts/AuthLayout";
import SignInPage from "./pages/auth/SignInPage";
import SignUpPage from "./pages/auth/SignUpPage";
import AccountLayout from "@/components/layouts/AccountLayout";
import Home from "@/app/pages/root/Home";
import SecurePage from "@/app/pages/auth/SecurePage";
import {AuthProvider} from "@/stores/AuthContext";
import {useAuth} from "@/hooks/useAuth";

type ProtectedRouteType = { children: ReactNode };

const App: FC = () => {
    const ProtectedRoute = ({children}: ProtectedRouteType) => {
        const {isAuthenticated} = useAuth();

        if (!isAuthenticated) {
            return <Navigate to="/sign-in" replace/>
        }

        return children;
    };

    const router = createBrowserRouter([
        {
            path: '/',
            element: <AuthLayout/>,
            children: [
                // Default to Sign In
                {index: true, Component: SignInPage},
                {path: 'sign-in', Component: SignInPage},
                {path: 'sign-up', Component: SignUpPage},
                {path: 'secure', Component: SecurePage}
            ],
        },
        {
            path: '/account',
            element: <ProtectedRoute><AccountLayout/></ProtectedRoute>,
            children: [
                {index: true, Component: Home}
            ]
        },
        {
            path: '*',
            element: (<div>404 - Page Not Found. Error Boundary To be added</div>),
        }
    ]);

    return (
        <AuthProvider>
            <RouterProvider router={router}/>
        </AuthProvider>
    );
};

export default App;
