import {createContext, ReactNode, useEffect, useMemo, useState} from "react";
import {authLogin} from "@/app/api/auth";
import {addNotification} from "@/lib/utils";
import {verifyOtp} from "@/app/api/otp";
import {setupInterceptors} from "@/lib/axiosApi";

interface AuthState {
    accessToken: string | null;
    refreshToken: string | null;
    isAuthenticated: boolean;
}

interface AuthContextType extends AuthState {
    authenticate: (data: SignInFormData) => Promise<{ status: boolean }>;
    otpVerification: (otp: string) => Promise<void>;
    logout: () => void;
}

export const AuthContext = createContext<AuthContextType | null>(null);

type AuthProviderProps = { children: ReactNode };

export const AuthProvider = ({children}: AuthProviderProps) => {
    const [auth, setAuth] = useState<AuthState>({
        accessToken: null,
        refreshToken: null,
        isAuthenticated: false
    });

    useEffect(() => {
        const handleLogout = () => logout();
        window.addEventListener('logout', handleLogout);
        return () => window.removeEventListener('logout', handleLogout);
    }, []);

    useEffect(() => {
        setupInterceptors(
            () => auth.accessToken,
            () => auth.refreshToken,
            (access, refresh) =>
                setAuth({
                    accessToken: access,
                    refreshToken: refresh,
                    isAuthenticated: true
                }),
            logout
        );
    }, [auth.accessToken, auth.refreshToken]);

    const authenticate = async (data: SignInFormData): Promise<{ status: boolean }> => {
        try {
            const authenticationResponse = await authLogin(data);
            const {status, token} = authenticationResponse;

            if (!token) return { status };

            setAuth((prevState) => ({...prevState, accessToken: token}));

            return { status };
        } catch (error: unknown) {
            const err = error as ApiError;
            addNotification({
                type: "error",
                message: "Authentication Error!",
                description: err.message,
            });

            return { status: false };
        }
    };

    const otpVerification = async (otp: string) => {
        try {
            return await verifyOtp(otp);
        } catch (error: unknown) {
            const err = error as ApiError;
            addNotification({
                type: "error",
                message: "Authorization Error!",
                description: err.message,
            });
        }
    };

    const logout = () => {
        setAuth({
            accessToken: null,
            refreshToken: null,
            isAuthenticated: false
        });
    };

    const providerValues = useMemo(() => ({
        ...auth, authenticate, otpVerification, logout
    }), []);

    return (
        <AuthContext.Provider value={providerValues}>
            {children}
        </AuthContext.Provider>
    );
};
