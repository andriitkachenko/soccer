/*
import React from 'react';
import Menu from '@material-ui/core/Menu';
import MenuItem from '@material-ui/core/MenuItem';
import Button from '@material-ui/core/Button';
import IconButton from '@material-ui/core/IconButton';
import MenuIcon from '@material-ui/icons/Menu';

const NavMenu = () => {
  const [anchorEl, setAnchorEl] = React.useState(null);

  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  return (
    <div>
        <IconButton edge="start" color="inherit" aria-controls="simple-menu" aria-label="menu" onClick={handleClick}>
            <MenuIcon />
        </IconButton>

      <Menu
        id="simple-menu"
        anchorEl={anchorEl}
        keepMounted
        open={Boolean(anchorEl)}
        onClose={handleClose}
      >
        <MenuItem onClick={handleClose}>Profile</MenuItem>
        <MenuItem onClick={handleClose}>My account</MenuItem>
        <MenuItem onClick={handleClose}>Logout</MenuItem>
      </Menu>
    </div>
  );
}
*/
import React from 'react';
import clsx from 'clsx';
import { makeStyles } from '@material-ui/core/styles';
import Drawer from '@material-ui/core/Drawer';
import IconButton from '@material-ui/core/IconButton';
import MenuIcon from '@material-ui/icons/Menu';
import { DirectionsRun, StarBorder, DoneAll, Lock } from '@material-ui/icons';
import { List, ListItem, ListItemText, ListItemIcon } from '@material-ui/core';
import Divider from '@material-ui/core/Divider';

const useStyles = makeStyles({
    list: {
        width: 250
    },
    fullList: {
        width: 'auto'
    }
});

const makeDateStr = () => {
    const today = new Date();
    const d = today.getDate();
    const m = today.getMonth() + 1;
    return `${d < 10 ? '0' + d : d}-${m < 10 ? '0' + m : m}-${today.getFullYear()}`;
}

const NavMenu = ({count}) => {
    const classes = useStyles();
    const [state, setState] = React.useState({
        open : false
    });

    const toggleDrawer = (open) => (event) => {
        if (event.type === 'keydown' && (event.key === 'Tab' || event.key === 'Shift')) {
            return;
        }

        setState({open});
    };

    const list = () => (
        <div
            className={clsx(classes.list)}
            role="presentation"
            onClick={toggleDrawer(false)}
            onKeyDown={toggleDrawer(false)}
        >
            <List>{
                [
                    `${makeDateStr()}`, 
                    `${count} games`
                ]
                .map((text, index) => (
                    <ListItem button key={index}>
                        <ListItemText primary={text} />
                    </ListItem>
                ))
            }</List>
            <Divider />
            <List>{
                [
                    {text: 'Live', icon: <DirectionsRun/>, allowed: true },
                    {text: 'Favourite', icon: <StarBorder/>,  allowed: false },
                    {text: 'Finished', icon: <DoneAll/>,  allowed: false }
                ]
                .map((data, index) => (
                    <ListItem button key={index}>
                        {data.icon ? <ListItemIcon>{ data.allowed ? data.icon : <Lock/>}</ListItemIcon> : null}
                        <ListItemText primary={data.text} />
                    </ListItem>
                ))
            }</List>            
        </div>
    );

    return (
        <div>
            <React.Fragment key='left'>
                <IconButton onClick={toggleDrawer(true)} edge="start"  color="inherit">
                    <MenuIcon />
                </IconButton>
                <Drawer 
                    open={state['open']} 
                    onClose={toggleDrawer(false)}
                >
                    {list()}
                </Drawer>
            </React.Fragment>
        </div>
    );
}

export default NavMenu;
