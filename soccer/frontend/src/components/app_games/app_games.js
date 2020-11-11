import React from 'react';
import DataService from '../../services/data-service';

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
                            const startUTC = this.getLocalSeconds(g.start_real);
                            const nowUTC = this.getLocalSeconds();
                            var m = Math.floor(((nowUTC - startUTC) / 60));
                            if (g.state === 3) m = 45 + m
                            else if (g.state === 4) m = 90 + m
                            g.time = m;
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
                            var m = '' + g.time;
                            if (g.state === 1 && g.time > 45) m = '45+' + (g.time - 45)
                            if (g.state === 2) m = 'HT'
                            if (g.state === 3 && g.time > 90) m = '90+' + (g.time - 90)
                            g.time = m;
                            return g;
                        });
                    this.setState({games : games});
                }
            });
        this.updating = false;
    }

    getLocalSeconds(utcString) {
        const local = utcString ? this.getDateLocal(utcString) : new Date();
        return Math.floor(local.getTime() / 1000);
    }

    getDateLocal(utcString) {
        var local = new Date(this.makeISOUTCString(utcString)).toString();
        return new Date(local);
    }

    getLocalTimeString(utcString) {
        var local = this.getDateLocal(utcString);
        const h = local.getHours();        
        const m = local.getMinutes();        
        return (h < 10 ? '0' : '') + h + ":" + (m < 10 ? '0' : '') + m;
    }

    makeISOUTCString(utcString) {
        return utcString.replace(' ', 'T') + 'Z';
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
        const gameList = games.map((game) => {
            return (
                <li className = 'item-list list-group-item' key={game.id}>
                    <table className="table">
                        <tbody>
                        <tr>
                            <td className='league'>{game.league} {}</td>
                            <td className='time game' rowSpan="2">{game.time}</td>
                            <td className='teams'>{ game.host } <span className="rank"> { game.host_rank ? game.host_rank : ''}</span></td>
                            <td className='stat gl'>{game.host_stat.gl}</td>
                            <td className='time' rowSpan="2">{game.min}</td>
                            <td className='stat long'>{game.host_stat.sh} - {game.host_stat.sg}</td>
                            <td className='stat long'>{game.host_stat.at} - {game.host_stat.da}</td>
                            <td className='stat'>{game.host_stat.bp}</td>
                            <td className='stat'>{game.host_stat.rc}</td>
                        </tr>
                        <tr>
                            <td className='league'>{this.getLocalTimeString(game.start_time)}</td>
                            <td className='teams'>{game.guest}</td>
                            <td className='stat gl'>{game.guest_stat.gl}</td>
                            <td className='stat long'>{game.guest_stat.sh} - {game.guest_stat.sg}</td>
                            <td className='stat long'>{game.guest_stat.at} - {game.guest_stat.da}</td>
                            <td className='stat'>{game.guest_stat.bp}</td>
                            <td className='stat'>{game.guest_stat.rc}</td>
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