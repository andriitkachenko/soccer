import Utils from '../../utils';

import { Shots, League, Team, StartTime, Scores, BallPossession, Time } from '../blocks';

import './game.css';

const Game = ({game}) => {
    const g = game;
    const extraClass = g.state === 2 ? ' ht' : '';
    return (
        <div className={"game" + extraClass}>
            <div className={"data" + extraClass}>
                <League title={g.league}/>
                <StartTime time={g.start_time}/>
                <Time time={g.time} extra={g.extra}/>
                <Team host title={g.host} rank={g.host_rank} rc={g.host_stat.rc} yc={g.host_stat.yc}/>
                <Team guest title={g.guest} rank={g.guest_rank} rc={g.guest_stat.rc} yc={g.guest_stat.yc}/>
                <Scores host all={g.host_stat.gl}/>
                <Scores host h1={g.ht ? g.ht.host_stat.gl : g.host_stat.gl}/>
                <Scores host h2={g.ht ? g.host_stat.gl - g.ht.host_stat.gl : null}/>
                <Scores guest all={g.guest_stat.gl}/>
                <Scores guest h1={g.ht ? g.ht.guest_stat.gl : g.guest_stat.gl}/>
                <Scores guest h2={g.ht ? g.guest_stat.gl - g.ht.guest_stat.gl : null}/>
            </div>
            <div className={"stat" + extraClass}>
                <Time time={g.min} extra={g.min_extra} />
                <Shots 
                    host
                    half1
                    state={g.state}                    
                    sh={g.ht ? g.ht.host_stat.sh : g.host_stat.sh} 
                    sg={g.ht ? g.ht.host_stat.sg : g.host_stat.sg} 
                />
                <Shots 
                    host
                    half2
                    state={g.state}                    
                    sh={g.ht ? g.host_stat.sh - g.ht.host_stat.sh : null} 
                    sg={g.ht ? g.host_stat.sg - g.ht.host_stat.sg : null} 
                />
                <Shots 
                    host
                    last10
                    sh={g.history2 ? g.host_stat.sh - g.history2.host_stat.sh : null} 
                    sg={g.history2 ? g.host_stat.sg - g.history2.host_stat.sg : null} 
                />
                <BallPossession 
                    host
                    all={g.host_stat.bp} 
                    half1={g.ht ? g.ht.host_stat.bp : null}
                    min={g.min}
                />
                <Shots 
                    guest
                    half1
                    state={g.state}
                    sh={g.ht ? g.ht.guest_stat.sh : g.guest_stat.sh} 
                    sg={g.ht ? g.ht.guest_stat.sg : g.guest_stat.sg} 
                />                                
                <Shots 
                    guest
                    half2
                    state={g.state}
                    sh={g.ht ? g.guest_stat.sh - g.ht.guest_stat.sh : null} 
                    sg={g.ht ? g.guest_stat.sg - g.ht.guest_stat.sg : null} 
                />                                
                <Shots 
                    guest
                    last10
                    sh={g.history2 ? g.guest_stat.sh - g.history2.guest_stat.sh : null} 
                    sg={g.history2 ? g.guest_stat.sg - g.history2.guest_stat.sg : null} 
                />
                <BallPossession 
                    guest
                    all={g.guest_stat.bp} 
                    half1={g.ht ? g.ht.guest_stat.bp : null}
                    min={g.min}
                />
                <div className='emptyA'></div>
            </div>
        </div>
    );
}

export default Game;