const setSortAction = (newSort) => {
    return {
        type : 'SET_SORT',
        payload : newSort
    }
}

const setFilterAction = (newFilter) => {
    return {
        type : 'SET_FILTER',
        payload : newFilter
    }
}

const setFavoritesAction = (gameId) => {
    return {
        type : 'SET_FAVORITES',
        payload : gameId
    }
}

const resetStateAction = () => {
    return {
        type : 'RESET_STATE'
    }
}

const gamesRequested = () => {
    return {
        type : 'GAMES_REQUESTED'
    }
}

const gamesLoaded = (games) => {
    return {
        type : 'GAMES_LOADED',
        payload : games
    }
}

const gamesLoadingError = (err) => {
    return {
        type : 'GAMES_LOADING_ERROR',
        payload : err
    }
}

const updateGamesAction = (dataService, dispatch) => {
    dispatch(gamesRequested());
    
    dataService
        .getLastStats()
        .then((stats) => {
            dispatch(gamesLoaded(stats !== false ? Object.values(stats) : false))
        })
        .catch((err) => {
            dispatch(gamesLoadingError(err));
        });
}

export {
    setSortAction,
    setFilterAction,
    setFavoritesAction,
    resetStateAction,
    updateGamesAction
}