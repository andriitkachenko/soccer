const devtrace = (() => {
    const debug = (new URLSearchParams(window.location.search)).get('debug');
    return (text) => { if (debug) console.log(text) };
})();

export {devtrace};