import {createContext, ReactNode, useEffect, useMemo, useRef, useState} from "react";
import {authLogin} from "@/app/api/auth";
import {addNotification} from "@/lib/utils";
import {verifyOtp} from "@/app/api/otp";
import {setupInterceptors} from "@/lib/axiosApi";

interface AuthState {
    accessToken: string | null;
    isAuthenticated: boolean;
}

interface AuthContextType extends AuthState {
    authenticate: (data: SignInFormData) => Promise<{ status: boolean; message?: string }>;
    otpVerification: (otp: string) => Promise<{ status: boolean; message?: string; }>;
    logout: () => void;
}

export const AuthContext = createContext<AuthContextType | null>(null);

type AuthProviderProps = { children: ReactNode };

export const AuthProvider = ({children}: AuthProviderProps) => {
    const tokenRef = useRef<string | null>(null);
    const [auth, setAuth] = useState<AuthState>({
        accessToken: null,
        isAuthenticated: false
    });

    // ✅ Logout handler (stable)
    const logout = () => {
        setAuth({
            accessToken: null,
            isAuthenticated: false
        });

        // optional: redirect handled by interceptor
        window.location.href = "/";
    };

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
                isAuthenticated: true
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
            authenticate,
            otpVerification,
            logout
        }),
        [auth.accessToken, auth.isAuthenticated]
    );

    return (
        <AuthContext.Provider value={providerValues}>
            {children}
        </AuthContext.Provider>
    );
};
