import '../styles/app.css';

// @ts-ignore
import React from "react";
import {createRoot} from "react-dom/client";
import App from "./App";

const container = document.getElementById('app');

if (container) {
    createRoot(container).render(<App />);
}

throw new Error('Unable to resolve content');
