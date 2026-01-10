const baseURL = window.location.origin + "/";

const scripts = [
    "assets/libs/toastify-js/src/toastify.js",
    "assets/libs/choices.js/public/assets/scripts/choices.min.js",
    "assets/libs/flatpickr/flatpickr.min.js"
];

scripts.forEach(script => {
    const scriptTag = document.createElement("script");
    scriptTag.src = baseURL + script;
    scriptTag.type = "text/javascript";
    document.head.appendChild(scriptTag);
});
