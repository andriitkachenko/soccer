import {DEFAULT_FILTER, FILTER_15MIN, FILTER_FAVORITE, FILTER_PREDICTABLE, FILTER_HALFTIME} from '../components/filter';
import {DEFAULT_SORT, SORT_TIME, SORT_LEAGUE, SORT_SHOTS} from '../components/sort';
import { Utils, GameUtils, devtrace } from '../utils';
import Storage from '../components/storage';

const appStorage = new Storage();

const makeDefaultState = () => {
    return {
        favorites : [],
        filter : DEFAULT_FILTER,
        sort : DEFAULT_SORT,
        games : null,
        waiting : false,
    }
}

const getStateFromStorageOrDefault = () => {
    const defState = makeDefaultState();
    const storage  = appStorage.get();
    if (!storage) {
        return defState;
    }
    devtrace(storage);
    return {
        ...defState,
        favorites : storage.favorites,
        filter : storage.filter,
        sort : storage.sort
    }
}

const updateFavorites = (favorites, gameId) => {
    if (!gameId) {
        return favorites;
    }
    if (favorites.includes(gameId)) {
        return favorites.filter((id) => id !== gameId);
    }
    favorites.push(gameId);
    return favorites;
}

const sortComparator = (sort) => (a, b) => { 
    let s;
    const compareStarred = (a, b) => +b.starred - +a.starred;
    const compareState = (a, b) => b.state - a.state;
    const compareTime = (a, b) => b.time - a.time;
    const compareLeague = (a, b) => a.league === b.league ? 0 : (a.league > b.league ? 1 : -1);
    const compareId = (a, b) => a.id - b.id;
    const compareShots = (a, b) => GameUtils.getHalfMaxShots(b) - GameUtils.getHalfMaxShots(a);
    const compareShotsOnGoal = (a, b) => GameUtils.getHalfMaxShotsOnGoal(b) - GameUtils.getHalfMaxShotsOnGoal(a);

    if ((s = compareStarred(a, b))) return s;
    switch(sort) {
        case SORT_TIME: 
            if ((s = compareState(a, b))) return s;
            if ((s = compareTime(a, b))) return s;
            if ((s = compareLeague(a, b))) return s;
            break;
        case SORT_SHOTS: 
            if ((s = compareShots(a, b))) return s;
            if ((s = compareShotsOnGoal(a, b))) return s;
            if ((s = compareState(a, b))) return s;
            if ((s = compareTime(a, b))) return s;
            if ((s = compareLeague(a, b))) return s;
            break;
        case SORT_LEAGUE: 
            if ((s = compareLeague(a, b))) return s;
            if ((s = compareState(a, b))) return s;
            if ((s = compareTime(a, b))) return s;
            break;
        default : break;
    }
    return compareId(a, b);
}

const applyFilter = (filter) => (game) => {
    let ok = game.starred;
    if (!ok) {
        switch(filter) {
            case FILTER_15MIN : ok = GameUtils.isHalfEnding(game); break;
            case FILTER_HALFTIME : ok = game.state === 2; break;
            case FILTER_PREDICTABLE : ok = GameUtils.isPredictable(game); break;
            case FILTER_FAVORITE : ok = false; break;
            default : ok = true;
        }
    }
    game.hide = !ok;
    return game;
}

const updateGames = (games, sort, filter, favorites) => {
    return filterGames(
                sortGames(
                    applyFavorites(games, favorites),
                    sort
                ),
                filter
            );
}

const setTime = (games) => {
    return games
        .map((g) => {
            let extra = 0;
            let m = Utils.getGameMinutes(g);
         
            if (g.state === 1 && m > 45) extra = m - 45
            else if (g.state === 3 && m > 90) extra = m - 90;

            if (g.state === 1 && m > 45) m = 45
            else if (g.state === 2) m = 'HT'
            else if (g.state === 3 && m > 90) m = 90

            g.time = m;
            g.extra = extra;
            return g;
        }) 
    
}

const refineGames = ({ filter, sort, favorites }) => (games) => {
    return updateGames(setTime(games), sort, filter, favorites);
}

const sortGames= (games, sort) => {
    return games.map((g) => Object.assign({}, g)).sort(sortComparator(sort));
}

const applyFavorites = (games, favorites) => {
    return games.map((g) => {
        g.starred = favorites.includes(g.id);
        return g;
    });
}

const filterGames = (games, filter) => {
    return games.map(applyFilter(filter)) 
}

const getStorageData = ({sort, filter, favorites}) => {
    return { sort, filter, favorites };
}

const reducer = (state, action) => {
    if (state === undefined) {
        return getStateFromStorageOrDefault();
    }
    
    devtrace("reducer " + action.type);  
    
    switch(action.type) {
        case 'SET_SORT' : {
            devtrace(action.payload);  
            if (state.sort === action.payload) {
                return state;
            }
            appStorage.set({...getStorageData(state), sort : action.payload });
            return { 
                ...state, 
                sort : action.payload,
                games : sortGames(state.games, action.payload)
            }
        }
        case 'SET_FILTER' : {
            devtrace(action.payload);  
            appStorage.set({...getStorageData(state), filter : action.payload })
            return { 
                ...state, 
                filter : action.payload,
                games : filterGames(state.games, action.payload)
            }
        }
        case 'SET_FAVORITES' : {
            devtrace(action.payload);  
            const { sort, filter, favorites, games } = state;
            const newFavorites = updateFavorites(favorites, action.payload);
            appStorage.set({...getStorageData(state), favorites : newFavorites })
            return { 
                ...state, 
                favorites : newFavorites,
                games : updateGames(games, sort, filter, newFavorites)
            }
        }
        case 'RESET_STATE' : {
            const newState = makeDefaultState();
            const { games } = state;
            const { sort, filter, favorites } = newState;
            appStorage.reset();
            return { 
                ...newState, 
                games : updateGames(games, sort, filter, favorites)
            }
        }
        case 'GAMES_LOADED' : {
            if (action.payload === false) {
                return state;
            }
            devtrace("reducer loaded games = " + action.payload.length);
            return { 
                ...state, 
                games : refineGames(state)(action.payload),
                waiting : true
            }
        }
        case 'GAMES_REQUESTED' : {
            return {
                ...state,
                waiting : false
            }
        }
        case 'GAMES_LOADING_ERROR' : {
            devtrace("reducer error: " + action.payload);
            return {
                ...state,
                waiting : false
            }
        }

        default : {
            devtrace("reducer default");
            return state;
        }
    }
}

export default reducer;