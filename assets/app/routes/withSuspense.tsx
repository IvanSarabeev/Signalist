import {ComponentType, LazyExoticComponent} from "react";
import SuspenseWrapper from "./SuspenseWrapper";

const withSuspense = (
    Component: LazyExoticComponent<ComponentType<any>> // Strict approach
    // Component: LazyExoticComponent<() => JSX.Element> // Lose approach
) => {
    return (
        <SuspenseWrapper>
            <Component/>
        </SuspenseWrapper>
    );
};

export default withSuspense;
