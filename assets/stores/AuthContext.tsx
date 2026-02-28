import {createContext, FC, ReactNode, useContext, useState} from "react";
import {setAuthToken} from "@/lib/axiosApi";
import {authLogin} from "@/app/api/auth";
import {addNotification} from "@/lib/utils";
import {verifyOtp} from "@/app/api/otp";

interface AuthState {
    user: any | null; // TODO: Create the base User model
    token: string | null;
    isAuthenticated: boolean;
}

interface AuthContextType extends AuthState {
    login: (parameters: SignInFormData) => Promise<void>;
    verifyOtp: (otp: string) => Promise<void>;
    logout: () => void;
}

const AuthContext = createContext<AuthContextType | null>(null);

type AuthProviderProps = {children: ReactNode};

export const AuthProvider = ({children}: AuthProviderProps) => {
    const [auth, setAuth] = useState<AuthState>({
        user: null,
        token: null,
        isAuthenticated: false
    });

    const authenticate = async (data: SignInFormData) => {
        try {
            const {status, token} = await authLogin(data);

            if (status && token) {
                setAuth((prevState) => ({...prevState, token}));
            }
        } catch (error: unknown) {
            const err = error as ApiError;
            addNotification({
                type: "error",
                message: "Authentication Error!",
                description: err.message,
            });
        }
    };

    const otpVerification = async (otp: string) => {
        try {
            const {status, data: {user, token}} = await verifyOtp(otp);
            if (status) {
                const finalToken = token;

                setAuth({
                    user,
                    token: finalToken,
                    isAuthenticated: true
                });

                setAuthToken(finalToken);
            }
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
        setAuthToken(undefined);
        setAuth({
            user: null,
            token: null,
            isAuthenticated: false
        });
    };

    return (
        <AuthContext.Provider value={{ ...auth, authenticate, otpVerification, logout}}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) throw new Error("useAuth must be used inside an AuthProvider!");
    return context;
};
