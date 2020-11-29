import Utils from '../../utils';

import { Shots, League, Team, StartTime, Scores, BallPossession, RedCard } from '../blocks';

import './game.css';

const Game = ({game}) => {
    const g = game;
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
}

export default Game;