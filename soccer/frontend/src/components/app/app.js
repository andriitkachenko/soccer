import React from 'react';

import AppHeader from '../app_header';
import AppGames from '../app_games';
import AppFooter from '../app_footer';

import DataService from '../../services/data-service';
import {NO_FILTER} from '../filter';
import Favorites from '../favorites';
import {SORT_TIME} from '../sort';

import './app.css';

const dataService = new DataService();

class App extends React.Component {
    timerId = null;
    updating = false;
    
    state = {
        favorites : new Favorites(),
        filter : NO_FILTER,
        sort : SORT_TIME,
        games : null
    }

    updateState() {
        if (this.updating)
            return;

        this.updating = true;
        dataService
            .getLastStats()
            .then((stats) => {
                if (stats !== false) {
                    this.setState({
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
        this.updateState();
        if (!this.timerId) 
            this.timerId = setInterval(() => this.updateState(), 60000);
    }

    componentWillUnmount() {
       clearInterval(this.timerId);
    }

    setFilter(filter) {
        this.setState({
            favorites : this.state.favorites,
            filter : filter, 
            sort : this.state.sort,
            games : this.state.games
        });
    }

    setSort(sort) {
        this.setState({
            favorites : this.state.favorites,
            filter : this.state.filter, 
            sort : sort,
            games : this.state.games
        });
    }

    render() {
        const {filter, sort, games} = this.state;
     
        return (
            <div className='app'>
                <AppHeader 
                    count={games ? games.length : 0} 
                    setFilter={(filter) => this.setFilter(filter) } 
                    setSort={(sort) => this.setSort(sort)}
                />
                <AppGames 
                    games={games} 
                    filter={filter} 
                    sort={sort}/>
{/*                <AppFooter count={games ? games.length : 0}/> */ }
            </div>
        );
    }
}

export default App;