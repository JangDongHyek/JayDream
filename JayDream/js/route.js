class JayDreamRoute {
    go(name, params = {}, replace = false) {
        const url = new URL(location.href);
        url.searchParams.set('is', name);
        for (const [k, v] of Object.entries(params)) url.searchParams.set(k, v);

        replace ? history.replaceState({}, '', url) : history.pushState({}, '', url);
        window.dispatchEvent(new PopStateEvent('popstate'));
    }
}