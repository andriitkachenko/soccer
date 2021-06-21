import { Cards } from "../../blocks";
import {Utils} from "../../../utils";
import classNames from 'classnames';

import './team.css';

const Team = ( {host, guest, title, rank, rc, yc} ) => {
    const clsName = classNames('team', {h : host, g : guest});
    return (
        <div className={clsName }>
            {title} {Utils.makeSuperscript(rank)} 
            <Cards red amount={rc ? rc : 0}/> 
            <Cards yellow amount={yc ? yc : 0}/>
        </div>
    );
}

export default Team;