import React from 'react';

import DataService from '../../services/data-service';
import Utils from '../../utils';

import Game from '../game';
import { Loader } from '../blocks';

import './app_games.css';

const dataService = new DataService();

class AppGames extends React.Component {
    timerId = null;
    updating = false;
    
    state = {
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
                    const games = Object.values(stats)
                        .map((g) => {
                            g.time = Utils.getGameMinutes(g);
                            return g;
                        })
                        .sort((a, b) => { 
                            var s = b.state - a.state; 
                            if (s) return s;
                            s = b.time - a.time;
                            if (s) return s;
                            s = a.league === b.league ? 0 : (a.league > b.league ? 1 : -1);
                            if (s) return s;
                            s = a.id - b.id;
                            return s;
                        })
                        .map((g) => {
                            var extra = 0;
                            if (g.state === 1 && g.time > 45) extra = g.time - 45
                            else if (g.state === 3 && g.time > 90) extra = g.time - 90;
                            var m = g.time;
                            if (g.state === 1 && g.time > 45) m = 45
                            else if (g.state === 2) m = 'HT'
                            else if (g.state === 3 && g.time > 90) m = 90
                            g.time = m;
                            g.extra = extra;
                            return g;
                        });
                    this.setState({games : games});
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

    render() {
        const { games } = this.state;
        if (!games) {
            return <Loader/>
        }
        if (!games.length) {
            return <div className="app-games no-game">No live games with statistic at the moment</div>
        }

        const gameList = games.map((game) => {
            return (
                <li className='item-list list-group-item' key={game.id}>
                    <Game game={game}/>
                </li>
            );
        });

        return (
            <div className='app-games'>
                 <ul className = 'item-list list-group'>
                    {gameList}
                </ul>
            </div>
        );
    }
}

export default AppGames;