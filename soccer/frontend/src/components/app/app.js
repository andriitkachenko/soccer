import React from 'react';

import AppHeader from '../app_header';
import AppGames from '../app_games';
//import AppFooter from '../app_footer';

import DataService from '../../services/data-service';
import {DEFAULT_FILTER} from '../filter';
import {DEFAULT_SORT} from '../sort';
import Storage from '../storage';

import './app.css';

const dataService = new DataService();
const appStorage = new Storage();

class App extends React.Component {
    timerId = null;
    updating = false;

    defState = {
        favorites : [],
        filter : DEFAULT_FILTER,
        sort : DEFAULT_SORT,
        games : null
    }

    state = this.getStateFromStorage(this.defState);

    getStateFromStorage(defState) {
        const { filter, sort, favorites, games} = defState;
        const { filter : oldFilter, sort : oldSort, favorites : oldFavorites} = appStorage.get();
        return {
            favorites : oldSort ? JSON.parse(oldFavorites) : favorites,
            filter : oldFilter ? oldFilter : filter,
            sort : oldSort ? oldSort : sort,
            games : games
        }
    }

    resetState() {
        const {games} = this.state;
        let newState = this.defState;
        newState['games'] = games;
        
        appStorage.reset();
        this.setState(newState);
    }

    updateState(state) {
        appStorage.set(state);    
        this.setState(state);
    }

    updateGames() {
        if (this.updating)
            return;

        this.updating = true;
        dataService
            .getLastStats()
            .then((stats) => {
                if (stats !== false) {
                    this.updateState({
                        favorites : this.state.favorites,
                        filter : this.state.filter,
                        sort : this.state.sort,
                        games : Object.values(stats)}
                    );
                }
            });
        this.updating = false;
    }

    componentDidMount() {
        this.updateGames();
        if (!this.timerId) 
            this.timerId = setInterval(() => this.updateGames(), 60000);
    }

    componentWillUnmount() {
       clearInterval(this.timerId);
    }

    setFilter(filter) {
        this.updateState({
            favorites : this.state.favorites,
            filter : filter, 
            sort : this.state.sort,
            games : this.state.games
        });
    }

    setSort(sort) {
        this.updateState({
            favorites : this.state.favorites,
            filter : this.state.filter, 
            sort : sort,
            games : this.state.games
        });
    }

    updateFavorites(gameId) {
        let favorites = this.state.favorites;
        if (!gameId) {
            return favorites;
        }
        if (favorites.includes(gameId)) {
            return favorites.filter((id) => id !== gameId);
        }
        favorites.push(gameId);
        return favorites;
    }

    setFavorites(gameId) {
        this.updateState({
            favorites : this.updateFavorites(gameId),
            filter : this.state.filter, 
            sort : this.state.sort,
            games : this.state.games
        });
    }

    render() {
        const {favorites, filter, sort, games} = this.state;

        return (
            <div className='app'>
                <AppHeader 
                    count={games ? games.length : 0} 
                    filter = {filter}
                    sort = {sort}
                    setFilter={(filter) => this.setFilter(filter) } 
                    setSort={(sort) => this.setSort(sort)}
                    resetState={() => this.resetState()}
                />
                <AppGames 
                    games={games} 
                    filter={filter} 
                    sort={sort}
                    favorites={favorites}
                    setFavorites={(gameId) => this.setFavorites(gameId)}
                />
{/*                <AppFooter count={games ? games.length : 0}/> */ }
            </div>
        );
    }
}

export default App;