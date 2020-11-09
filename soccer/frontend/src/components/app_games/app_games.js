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
        const stats = dataService
            .getLastStats()
            .then((stats) => {
                if (stats !== false) {
                   this.setState({games : Object.values(stats).sort((a, b) => { return b.min - a.min;})})
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
        const gameList = games.map((game) => {
            const time = new Date(game.start_time);
            const h = time.getHours();
            const m = time.getMinutes();
            
            return (
                <li className = 'item-list list-group-item' key={game.id}>
                    <table className="table">
                        <tbody>
                        <tr>
                            <td className='league'>{game.league} {}</td>
                            <td className='time game' rowSpan="2">{game.min}</td>
                            <td className='teams'>{ game.host } <span className="rank"> { game.host_rank ? game.host_rank : ''}</span></td>
                            <td className='stat gl'>{game.host_stat.gl}</td>
                            <td className='time' rowSpan="2">{game.min}</td>
                            <td className='stat long'>{game.host_stat.sh} - {game.host_stat.sg}</td>
                            <td className='stat long'>{game.host_stat.at} - {game.host_stat.da}</td>
                            <td className='stat'>{game.host_stat.bp}</td>
                            <td className='stat'>{game.host_stat.rc}</td>
                        </tr>
                        <tr>
                            <td className='league'>{(h < 10 ? '0' : '') + h + ":" + (m < 10 ? '0' : '') + m}</td>
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