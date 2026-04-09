import {useCallback, useState} from "react";

function useLocalStorage<T>(key: string, initialValue: T) {
    if (!key) throw new Error('Error Missing Key Storage');

    const [storedValue, setStoredValue] = useState(() => {
        try {
            let item = sessionStorage.getItem(key);

            if (item === null) {
                item = JSON.stringify(initialValue);
                sessionStorage.setItem(key, item);
            }

            return JSON.parse(item) as T;
        } catch (error: unknown) {
            console.error(`Error invalid reading from storage key: "${key}": `, error);

            return initialValue;
        }
    });

    const setValue = useCallback((value: T) => {
        setStoredValue(() => {
            sessionStorage.setItem(key, JSON.stringify(value));
            return value;
        });
    }, [key]);

    return [storedValue, setValue] as const;
}

export default useLocalStorage;
