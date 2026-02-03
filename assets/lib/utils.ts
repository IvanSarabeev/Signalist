import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";
import { ExternalToast, toast } from "sonner";
import { ReactNode } from "react";

export function cn(...inputs: any[]) {
    return twMerge(clsx(inputs));
}

type NotificationType = "success" | "error" | "warning" | "info";

interface NotificationOptions {
    type: NotificationType;
    message: string;
    description?: string;
    duration?: number;
    icons?: NotificationType;
    className?: string;
}

const defaultToastStyles = {
    success: "bg-green-500 text-white rounded-lg px-4 py-2 shadow-md font-semibold",
    error: "bg-red-500 text-white rounded-lg px-4 py-2 shadow-md font-semibold",
    warning: "bg-yellow-400 text-black rounded-lg px-4 py-2 shadow-md font-semibold",
    info: "bg-blue-500 text-white rounded-lg px-4 py-2 shadow-md font-semibold",
} as const;

export function addNotification({type, message, description, duration = 3000, icons, className}: NotificationOptions) {
    const toastFn: Record<NotificationType, (msg: ReactNode, opts?: ExternalToast) => string | number> = {
        success: toast.success,
        error: toast.error,
        warning: toast.warning,
        info: toast, // default toast
    };

    return toastFn[type](message, {
        description,
        duration,
        icon: icons,
        className: cn(defaultToastStyles[type], className),
    });
}
