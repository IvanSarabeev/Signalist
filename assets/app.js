import './styles/app.css';
import React, {StrictMode} from "react";
import {createRoot} from "react-dom/client";
import App from "./app/App";

const container = document.getElementById('app');

if (!container) {
    throw new Error('Unable to resolve content');
}

createRoot(container).render(
    <StrictMode>
        <App />
    </StrictMode>
);
