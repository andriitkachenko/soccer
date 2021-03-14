import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';
import NavMenu from '../blocks/menu';
import { FilterMenu, SortMenu} from '../blocks';

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

const AppHeader = function({count, setFilter, setSort}) {
  const classes = useStyles();

  return (
    <div className={classes.root}>
      <AppBar position="static">
        <Toolbar>
          <NavMenu count={count} />
          <Typography variant="h6" className={classes.title}>
            Live Soccer Stats
          </Typography>
          <FilterMenu setFilter={setFilter}/>
          <SortMenu setSort={setSort}/>
        </Toolbar>
      </AppBar>
    </div>
  );
}

export default AppHeader;