import React from 'react';
import { connect } from 'react-redux';

import AppHeader from '../app_header';
import AppGames from '../app_games';
import AppFooter from '../app_footer';

import DataService from '../../services/data-service';
import { updateGamesAction } from '../../actions';
import { devtrace } from '../../utils';

import './app.css';

const dataService = new DataService();

class App extends React.Component {
    timerId = undefined;

    componentDidMount() {
        devtrace("App componentDidMount"); 

        this.props.updateGames();
        this.timerId = setInterval(this.props.updateGames, 60000);
    }

    componentWillUnmount() {
        devtrace("App componentWillUnmount"); 

        clearInterval(this.timerId);
        this.timerId = undefined;
    }

    render() {
        return (
            <div className='app'>
                <AppHeader />
                <AppGames />
                <AppFooter />
            </div>
        );
    }
}

const dispatch2props = (dispatch) => {
    return {
        updateGames : () => updateGamesAction(dataService, dispatch),
    }
}

export default connect(null, dispatch2props)(App);