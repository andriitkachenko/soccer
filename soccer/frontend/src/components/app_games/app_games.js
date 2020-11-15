import React from 'react';

import DataService from '../../services/data-service';
import Utils from '../../utils';

import League from '../league';
import StartTime from '../start_time';

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
            return <span>No live games</span>
        }
        const gameList = games.map((g) => {
            return (
                <li className = 'item-list list-group-item' key={g.id}>
                    <table className="table">
                        <tbody>
                        <tr>
                            <td className='col_league'><League title={g.league}/></td>
                            <td className='time game' rowSpan="2">{g.time}{Utils.makeSuperscript(g.extra, '+')}</td>
                            <td className='teams'>{ g.host } {Utils.makeSuperscript(g.host_rank) }</td>
                            <td className='stat gl'>{g.host_stat.gl}</td>
                            <td className='time' rowSpan="2">{g.min}{Utils.makeSuperscript(g.min_extra, '+')}</td>
                            <td className='stat long'>{g.host_stat.sh} - {g.host_stat.sg}</td>
                            <td className='stat long'>{g.host_stat.at} - {g.host_stat.da}</td>
                            <td className='stat'>{g.host_stat.bp}</td>
                            <td className='stat'>{g.host_stat.rc}</td>
                        </tr>
                        <tr>
                            <td className='col_league'><StartTime time={g.start_time}/></td>
                            <td className='teams'>{g.guest} {Utils.makeSuperscript(g.guest_rank) }</td>
                            <td className='stat gl'>{g.guest_stat.gl}</td>
                            <td className='stat long'>{g.guest_stat.sh} - {g.guest_stat.sg}</td>
                            <td className='stat long'>{g.guest_stat.at} - {g.guest_stat.da}</td>
                            <td className='stat'>{g.guest_stat.bp}</td>
                            <td className='stat'>{g.guest_stat.rc}</td>
                        </tr>
                        </tbody>
                    </table>
                </li>
            );
        });
        return (
            <div className='app-games'>
                 <ul className = 'item-list list-group'>
                    { gameList }
                </ul>
            </div>
        );
    }
}

export default AppGames;