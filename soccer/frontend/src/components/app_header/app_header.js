import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';
import NavMenu from '../blocks/menu';
import { FilterMenu, SortMenu, WaitIndicator } from '../blocks';
import { connect } from 'react-redux';
import { setSortAction, setFilterAction, resetStateAction } from '../../actions';

import './app_header.css';

/*
class AppHeader extends React.Component {
    render() {
        return (
            <div className='app-header'>
                <div className='title'>Live Soccer Stats</div>
            </div>
        );
    }
}

*/

const useStyles = makeStyles((theme) => ({
  root: {
    flexGrow: 1,
  },
  menuButton: {
    marginRight: theme.spacing(2),
  },
  title: {
    flexGrow: 1,
  },
}));

const AppHeader = function({count, filter, sort, setFilter, setSort, resetState}) {
  const classes = useStyles();

  return (
    <div className={classes.root}>
      <AppBar position="static">
        <Toolbar>
          <NavMenu count={count} resetState={resetState}/>
          <Typography variant="h6" className={classes.title}>
            Soccer Tracker
          </Typography>
          <FilterMenu curFilter={filter} setFilter={setFilter}/>
          <SortMenu curSort={sort} setSort={setSort}/>
          <WaitIndicator />
        </Toolbar>
      </AppBar>
    </div>
  );
}

const state2props = ({ games, filter, sort }) => {
  const count = games ? games.length : 0;
  return { count, filter, sort }
}

const dispatch2props = (dispatch) => {
  return {
    setFilter: (filter) => dispatch(setFilterAction(filter)),
    setSort : (sort) =>  dispatch(setSortAction(sort)),
    resetState: () => dispatch(resetStateAction()),
  }
}

export default connect(state2props, dispatch2props)(AppHeader);