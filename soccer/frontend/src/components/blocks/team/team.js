import Utils from "../../../utils";
import './team.css';

const Team = ( {host, guest, title, rank} ) => {
    const clsName = 'team' + (host ? ' h' : '') + (guest ? ' g' : '');
    return (
        <div className={clsName }>
            {title}{Utils.makeSuperscript(rank)}
        </div>
    );
}

export default Team;