import React from 'react';
import clsx from 'clsx';
import { makeStyles } from '@material-ui/core/styles';
import Drawer from '@material-ui/core/Drawer';
import IconButton from '@material-ui/core/IconButton';
import MenuIcon from '@material-ui/icons/Menu';
import { DirectionsRun, DoneAll, Lock, SettingsBackupRestore } from '@material-ui/icons';
import { List, ListItem, ListItemText, ListItemIcon } from '@material-ui/core';
import Divider from '@material-ui/core/Divider';

import './menu.css';

const useStyles = makeStyles({
    list: {
        width: 250
    },
    fullList: {
        width: 'auto'
    }
});

const makeDateStr = () => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: '2-digit' };
    return (new Date()).toLocaleDateString('en-US', options);
}

const NavMenu = ({count, resetState}) => {
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

    const nop = () => {};
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
                    `${count} games with live stats`
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
                    {text: 'Live', icon: <DirectionsRun/>, allowed: true, onClick : nop },
                    {text: 'History', icon: <DoneAll/>,  allowed: false, onClick : nop },
                    {text: 'Reset', icon: <SettingsBackupRestore/>,  allowed: true, onClick : resetState }
                ]
                .map((data, index) => (
                    <ListItem button key={index} onClick={data.onClick}>
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
                <IconButton onClick={toggleDrawer(true)} edge="start"  color="inherit" disableRipple={true}>
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
