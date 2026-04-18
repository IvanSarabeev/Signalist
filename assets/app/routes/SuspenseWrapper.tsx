import React, {ReactNode, Suspense} from 'react'

const SuspenseWrapper = ({children}: {children: ReactNode}) => {
    return (
        <Suspense fallback={<div>Loading...</div>}>
            {children}
        </Suspense>
    );
}

export default SuspenseWrapper;
