import React, {FC, lazy} from 'react';
import {createBrowserRouter, RouterProvider} from "react-router-dom";
import {AuthProvider} from "@/stores/AuthContext";
import withSuspense from "@/app/routes/withSuspense";

const AuthLayout = lazy(() => import('@/components/layouts/AuthLayout'));
const AccountLayout = lazy(() => import('@/components/layouts/AccountLayout'));

const SignInAuthenticationPage = lazy(() => import('@/app/pages/auth/SignInPage'));
const SignUpAuthenticationPage = lazy(() => import('@/app/pages/auth/SignUpPage'));
const SecureTokenAuthenticationPage = lazy(() => import('@/app/pages/auth/SecurePage'));

const AccountDashboardPage = lazy(() => import('@/app/pages/root/Home'));

const router = createBrowserRouter([
    {
        path: '/',
        element: withSuspense(AuthLayout),
        children: [
            // Default to Sign In
            {index: true, element: withSuspense(SignInAuthenticationPage)},
            {path: 'sign-in', element: withSuspense(SignInAuthenticationPage)},
            {path: 'sign-up', element: withSuspense(SignUpAuthenticationPage)},
            {path: 'secure', element: withSuspense(SecureTokenAuthenticationPage)}
        ],
    },
    {
        path: '/account',
        element: withSuspense(AccountLayout),
        children: [
            {index: true, element: withSuspense(AccountDashboardPage)}
        ]
    },
    {
        path: '*',
        element: (<div>404 - Page Not Found. Error Boundary To be added</div>),
    }
]);

const App: FC = () => {
    return (
        <AuthProvider>
            <RouterProvider router={router}/>
        </AuthProvider>
    );
};

export default App;
