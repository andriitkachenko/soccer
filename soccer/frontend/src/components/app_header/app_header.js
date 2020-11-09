import React from 'react';

import './app_header.css';

class AppHeader extends React.Component {
    render() {
        return (
            <div className='app-header'>
               <table className="table">
                    <tbody>
                        <tr>
                            <td className='league'></td>
                            <td className='time'>Time</td>
                            <td className='teams'>Teams</td>
                            <td className='stat'>Scores</td>
                            <td className='time'>Min</td>
                            <td className='stat long'>Shots</td>
                            <td className='stat long'>Attacks</td>
                            <td className='stat'>bp</td>
                            <td className='stat'>Red</td>
                        </tr>
                    </tbody>
                </table>                
            </div>
        );
    }
}

export default AppHeader;