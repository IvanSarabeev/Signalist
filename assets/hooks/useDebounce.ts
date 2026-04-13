import {useCallback, useEffect, useRef} from "react";
import {clearTimeout} from "node:timers";


export function useDebounce(callback: () => void | Promise<void>, delay: number) {
    const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        return () => {
            if (timeoutRef.current) clearTimeout(timeoutRef.current);
        }
    }, []);

    return useCallback(() => {
        if (timeoutRef.current) {
            clearTimeout(timeoutRef.current);
        }

        timeoutRef.current = setTimeout(callback, delay);
    }, [callback, delay]);
}
