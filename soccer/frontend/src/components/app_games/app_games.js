import React from 'react';

import DataService from '../../services/data-service';
import Utils from '../../utils';

import { Shots, League, Team, StartTime, Scores, BallPossession, RedCard } from '../blocks';

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
                    <div className="game-container">
                        <div className="data">
                            <table className="table">
                            <tbody>
                            <tr>
                                <td className='col-league'><League title={g.league}/></td>
                                <td className='col-game-time' rowSpan="2">{g.time}{Utils.makeSuperscript(g.extra, '+')}</td>
                                <td className='teams'><Team title={g.host} rank={g.host_rank}/></td>
                                <td className='stat'><RedCard amount={g.host_stat.rc}/></td>
                                <td className='stat gl'>
                                    <Scores all ={g.host_stat.gl} half1={g.ht ? g.ht.host_stat.gl : null}/>
                                </td>
                                <td rowSpan="2"></td>
                            </tr>
                            <tr>
                                <td className='col-league'>
                                    <StartTime time={g.start_time}/>
                                </td>
                                <td className='teams'>
                                    <Team title={g.guest} rank={g.guest_rank}/>
                                </td>
                                <td className='stat'><RedCard amount={g.guest_stat.rc}/></td>
                                <td className='stat gl'>
                                    <Scores all ={g.guest_stat.gl} half1={g.ht ? g.ht.guest_stat.gl : null}/>
                                </td>
                            </tr>
                            </tbody>
                            </table>
                        </div>
                        <div className="stats">
                            <table className="table">
                            <tbody>
                            <tr>                            
                                <td className='col-game-time' rowSpan="2">{g.min}{Utils.makeSuperscript(g.min_extra, '+')}</td>
                                <td className='stat long'> 
                                    <Shots 
                                        shAll={g.host_stat.sh} 
                                        shHalf1={g.ht ? g.ht.host_stat.sh : null} 
                                        sgAll={g.host_stat.sg} 
                                        sgHalf1={g.ht ? g.ht.host_stat.sg : null} 
                                    />
                                </td>
                                <td className='stat long'> 
                                    <Shots 
                                        shAll={g.host_stat.sh} 
                                        shHalf1={g.history2 ? g.history2.host_stat.sh : null} 
                                        sgAll={g.host_stat.sg} 
                                        sgHalf1={g.history2 ? g.history2.host_stat.sg : null} 
                                        diff
                                    />
                                </td>
                                <td className='stat'>
                                    <BallPossession 
                                        all={g.host_stat.bp} 
                                        half1={g.ht ? g.ht.host_stat.bp : null}
                                        min={g.min}
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td className='stat long'>
                                    <Shots 
                                        shAll={g.guest_stat.sh} 
                                        shHalf1={g.ht ? g.ht.guest_stat.sh : null} 
                                        sgAll={g.guest_stat.sg} 
                                        sgHalf1={g.ht ? g.ht.guest_stat.sg : null} 
                                    />                                
                                </td>
                                <td className='stat long'> 
                                    <Shots 
                                        shAll={g.guest_stat.sh} 
                                        shHalf1={g.history2 ? g.history2.guest_stat.sh : null} 
                                        sgAll={g.guest_stat.sg} 
                                        sgHalf1={g.history2 ? g.history2.guest_stat.sg : null} 
                                        diff
                                    />
                                </td>                                
                                <td className='stat'>
                                    <BallPossession 
                                        all={g.guest_stat.bp} 
                                        half1={g.ht ? g.ht.guest_stat.bp : null}
                                        min={g.min}
                                    />
                                </td>
                            </tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
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