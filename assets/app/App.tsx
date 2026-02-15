import React, {FC} from 'react';
import {createBrowserRouter, RouterProvider} from "react-router-dom";
import AuthLayout from "../components/layouts/AuthLayout";
import SignInPage from "./pages/auth/SignInPage";
import SignUpPage from "./pages/auth/SignUpPage";
import AccountLayout from "@/components/layouts/AccountLayout";
import Home from "@/app/pages/root/Home";
import SecurePage from "@/app/pages/auth/SecurePage";

const router = createBrowserRouter([
    {
        path: '/',
        element: <AuthLayout />,
        children: [
            // Default to Sign In
            { index: true, Component: SignInPage },
            { path: 'sign-in', Component: SignInPage },
            { path: 'sign-up', Component: SignUpPage },
            { path: 'secure', Component: SecurePage }
        ],
    },
    {
        path: '/account',
        element: <AccountLayout />,
        children: [
            { index: true, element: <Home />}
        ]
    },
    {
        path: '*',
        element: (<div>404 - Page Not Found. Error Boundary To be added</div>),
    }
]);

const App: FC = () => <RouterProvider router={router} />;

export default App;
