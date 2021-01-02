import React from 'react';

import AppHeader from '../app_header';
import AppGames from '../app_games';
import AppFooter from '../app_footer';

import DataService from '../../services/data-service';

import './app.css';

const dataService = new DataService();

class App extends React.Component {
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
                    this.setState({games : Object.values(stats)});
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
        const {games} = this.state;
     
        return (
            <div className='app'>
                <AppHeader count={games ? games.length : 0}/>
                <AppGames games={games}/>
{/*                <AppFooter count={games ? games.length : 0}/> */ }
            </div>
        );
    }
}

export default App;