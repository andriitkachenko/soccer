import React from 'react';

import AppHeader from '../app_header';
import AppGames from '../app_games';
import AppFooter from '../app_footer';

import './app.css';

class App extends React.Component {
    render() {
        return (
            <div className='app'>
                <AppHeader/>
                <AppGames/>
                <AppFooter/>
            </div>
        );
    }
}

export default App;