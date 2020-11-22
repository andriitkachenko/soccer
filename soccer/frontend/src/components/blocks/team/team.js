import Utils from "../../../utils";
import './team.css';

const Team = ( {title, rank} ) => {
    return (
        <div className="team">
            {title}{Utils.makeSuperscript(rank)}
        </div>
    );
}

export default Team;