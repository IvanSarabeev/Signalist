import {createContext, ReactNode, useEffect, useMemo, useRef, useState} from "react";
import {authLogin, authLogout} from "@/app/api/auth";
import {addNotification} from "@/lib/utils";
import {verifyOtp} from "@/app/api/otp";
import {setupInterceptors} from "@/lib/axiosApi";
import {getCurrentUser} from "@/app/api/user";

interface AuthState {
    accessToken: string | null;
    isAuthenticated: boolean;
    user: User | null;
    isLoadingUser: boolean;
}

interface AuthContextType extends AuthState {
    authenticate: (data: SignInFormData) => Promise<{ status: boolean; message?: string }>;
    otpVerification: (otp: string) => Promise<{ status: boolean; message?: string; }>;
    logout: () => Promise<void>;
}

type AuthProviderProps = { children: ReactNode };

export const AuthContext = createContext<AuthContextType | null>(null);

const AUTH_STORAGE_KEY = 'storageKey';

export const AuthProvider = ({children}: AuthProviderProps) => {
    const tokenRef = useRef<string | null>(null);
    const [auth, setAuth] = useState<AuthState>(() => {
        try {
            const storedToken = sessionStorage.getItem(AUTH_STORAGE_KEY);

            if (!storedToken) {
                return { accessToken: null, isAuthenticated: false, user: null, isLoadingUser: false };
            }

            const token = JSON.parse(storedToken);

            return { accessToken: token, isAuthenticated: true, user: null, isLoadingUser: true };
        } catch {
            return { accessToken: null, isAuthenticated: false, user: null, isLoadingUser: false };
        }
    });

    useEffect(() => {
        tokenRef.current = auth.accessToken;
    }, [auth.accessToken]);

    // ✅ Setup interceptors ONLY ONCE
    useEffect(() => {
        setupInterceptors(
            () => tokenRef.current,
            logout
        );
    }, []); // 🔥 IMPORTANT: empty deps

    useEffect(() => {
        if (auth.accessToken) {
            sessionStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(auth.accessToken));
        } else {
            sessionStorage.removeItem(AUTH_STORAGE_KEY);
        }
    }, [auth.accessToken]);

    const fetchUser = async () => {
        try {
            const {status, data} = await getCurrentUser();

            setAuth((prevState) => ({
                ...prevState,
                user: status && data ? data : null,
                isLoadingUser: false
            }));
        } catch {
            setAuth((prevState) => ({
                ...prevState,
                user: null,
                isLoadingUser: false
            }));
        }
    };

    useEffect(() => {
        if (auth.isAuthenticated && !auth.user) {
            fetchUser();
        }
    }, [auth.isAuthenticated]);

    // ✅ Logout handler (stable)
    const logout = async (): Promise<void> => {
        return await authLogout()
            .finally(() => {
                setAuth({
                    accessToken: null,
                    isAuthenticated: false,
                    user: null,
                    isLoadingUser: false,
                });

                sessionStorage.removeItem(AUTH_STORAGE_KEY);
            });
    };

    // 🔐 Login
    const authenticate = async (
        data: SignInFormData
    ): Promise<{ status: boolean; message?: string }> => {
        try {
            const authenticationResponse = await authLogin(data);
            const {status, token, message} = authenticationResponse;

            if (!status || !token) {
                return {status: false, message};
            }

            tokenRef.current = token;

            setAuth({
                accessToken: token,
                isAuthenticated: false,
                user: null,
                isLoadingUser: false
            });

            return {status: true};
        } catch (error: unknown) {
            const err = error as ApiError;

            addNotification({
                type: "error",
                message: "Authentication Error!",
                description: err.message,
            });

            return {status: false};
        }
    };

    const otpVerification = async (otp: string) => {
        try {
            const {status} = await verifyOtp(otp);

            setAuth((prevState) => ({
                ...prevState,
                isAuthenticated: status
            }));

            return { status };
        } catch (error: unknown) {
            const err = error as ApiError;

            addNotification({
                type: "error",
                message: "Authorization Error!",
                description: err.message,
            });

            return { status: false };
        }
    };

    const providerValues = useMemo(
        () => ({
            accessToken: auth.accessToken,
            isAuthenticated: auth.isAuthenticated,
            user: auth.user,
            isLoadingUser: auth.isLoadingUser,
            authenticate,
            otpVerification,
            logout,
        }),
        [auth.accessToken, auth.isAuthenticated, auth.user, auth.isLoadingUser]
    );

    return (
        <AuthContext.Provider value={providerValues}>
            {children}
        </AuthContext.Provider>
    );
};
