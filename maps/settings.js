function dateUpdate(form) {
        var now = new Date();
        
        var start = new Date(document.forms[form]["start"].value);
        var end = new Date();
        var expires = new Date();

        var mode = document.forms[form]["mode"].value

        switch (mode) {
            case "1d":
                //set values
                start.setMinutes(0);
                start.setHours(now.getTimezoneOffset()/-60);
                end.setDate(start.getDate()+1);
                end.setMinutes(0);
                end.setHours(now.getTimezoneOffset()/-60);
                expires.setDate(start.getDate()+1);
                expires.setMinutes(0);
                expires.setHours(now.getTimezoneOffset()/-60);

                //update needed values
                document.forms[form]["start"].value = start.toISOString().slice(0,16);
                document.forms[form]["end"].value = end.toISOString().slice(0,16);
                document.forms[form]["expires"].value = expires.toISOString().slice(0,16);
                break;
            case "1w":
                //set values
                start.setMinutes(0);
                start.setHours(now.getTimezoneOffset()/-60);
                end.setDate(start.getDate()+7);
                end.setMinutes(0);
                end.setHours(now.getTimezoneOffset()/-60);
                expires.setDate(start.getDate()+7);
                expires.setMinutes(0);
                expires.setHours(now.getTimezoneOffset()/-60);

                //update needed values
                document.forms[form]["start"].value = start.toISOString().slice(0,16);
                document.forms[form]["end"].value = end.toISOString().slice(0,16);
                document.forms[form]["expires"].value = expires.toISOString().slice(0,16);
                break;
            default:
                break;
        }
    }
    function modeUpdate(form) {
        var now = new Date();

        var heatmap;
        var live;
        var start = new Date();
        var end = new Date();
        var expires = new Date();

        var mode = document.forms[form]["mode"].value

        switch (mode) {
            case "HeatMap":
                //set values
                heatmap = true;
                live = false;
                start.setFullYear(now.getFullYear() - 50);
                start.setMinutes(0);
                start.setHours(now.getTimezoneOffset()/-60);
                end.setFullYear(now.getFullYear() + 50);
                end.setMinutes(0);
                end.setHours(now.getTimezoneOffset()/-60);
                expires.setFullYear(now.getFullYear() + 50);
                expires.setMinutes(0);
                expires.setHours(now.getTimezoneOffset()/-60);

                //disable fixed settings in this mode
                document.forms[form]["heatmap"].disabled = true;
                document.forms[form]["live"].disabled = true;
                document.forms[form]["end"].disabled = true;
                document.forms[form]["start"].disabled = true;
                break;
            case "Live1h":
                //set values
                heatmap = false;
                live = true;
                start.setMinutes(now.getMinutes() - 1 - now.getTimezoneOffset());
                end.setMinutes(now.getMinutes() + 60 - now.getTimezoneOffset());
                expires.setMinutes(now.getMinutes() + 60 - now.getTimezoneOffset());

                //disable fixed settings in this mode
                document.forms[form]["heatmap"].disabled = true;
                document.forms[form]["live"].disabled = true;
                document.forms[form]["end"].disabled = true;
                document.forms[form]["start"].disabled = true;
                break;
            case "Live1d":
                //set values
                heatmap = false;
                live = true;
                start.setMinutes(now.getMinutes() - 10 - now.getTimezoneOffset());
                end.setMinutes(now.getMinutes() + (60*24) - now.getTimezoneOffset());
                expires.setMinutes(now.getMinutes() + (60*24)+10 - now.getTimezoneOffset());

                //disable fixed settings in this mode
                document.forms[form]["heatmap"].disabled = true;
                document.forms[form]["live"].disabled = true;
                document.forms[form]["end"].disabled = true;
                document.forms[form]["start"].disabled = true;
                break;
            case "Today":
                //set values
                heatmap = false;
                live = false;
                start.setMinutes(0);
                start.setHours(now.getTimezoneOffset()/-60);
                end.setDate(now.getDate()+1);
                end.setMinutes(0);
                end.setHours(now.getTimezoneOffset()/-60);
                expires.setMinutes(Math.round(now.getMinutes()/(24*60))*24*60);

                //disable fixed settings in this mode
                document.forms[form]["heatmap"].disabled = true;
                document.forms[form]["live"].disabled = true;
                document.forms[form]["end"].disabled = true;
                document.forms[form]["start"].disabled = true;
                break;
            case "AnyTime":
                //set values
                heatmap = false;
                live = false;
                start.setFullYear(now.getFullYear() - 50);
                start.setMinutes(0);
                start.setHours(now.getTimezoneOffset()/-60);
                end.setFullYear(now.getFullYear() + 50);
                end.setMinutes(0);
                end.setHours(now.getTimezoneOffset()/-60);
                expires.setFullYear(now.getFullYear() + 50);
                expires.setMinutes(0);
                expires.setHours(now.getTimezoneOffset()/-60);

                //disable fixed settings in this mode
                document.forms[form]["heatmap"].disabled = true;
                document.forms[form]["live"].disabled = true;
                document.forms[form]["end"].disabled = false;
                document.forms[form]["start"].disabled = false;
                break;
            case "1d":
                //set values
                heatmap = false;
                live = false;
                start.setMinutes(0);
                start.setHours(now.getTimezoneOffset()/-60);
                end.setDate(start.getDate()+1);
                end.setMinutes(0);
                end.setHours(now.getTimezoneOffset()/-60);
                expires.setDate(start.getDate()+1);
                expires.setMinutes(0);
                expires.setHours(now.getTimezoneOffset()/-60);

                //disable fixed settings in this mode
                document.forms[form]["heatmap"].disabled = false;
                document.forms[form]["live"].disabled = true;
                document.forms[form]["end"].disabled = true;
                document.forms[form]["start"].disabled = false;
                break;
            case "1w":
                //set values
                heatmap = false;
                live = false;
                start.setMinutes(0);
                start.setHours(now.getTimezoneOffset()/-60);
                end.setDate(start.getDate()+7);
                end.setMinutes(0);
                end.setHours(now.getTimezoneOffset()/-60);
                expires.setDate(start.getDate()+7);
                expires.setMinutes(0);
                expires.setHours(now.getTimezoneOffset()/-60);

                //disable fixed settings in this mode
                document.forms[form]["heatmap"].disabled = true;
                document.forms[form]["live"].disabled = true;
                document.forms[form]["end"].disabled = true;
                document.forms[form]["start"].disabled = false;
                break;
            default:
                //disable fixed settings in this mode
                document.forms[form]["heatmap"].disabled = false;
                document.forms[form]["live"].disabled = false;
                document.forms[form]["end"].disabled = false;
                document.forms[form]["start"].disabled = false;
                break;
        }

        //update needed values
        document.forms[form]["heatmap"].checked = heatmap;
        document.forms[form]["live"].checked = live;
        document.forms[form]["start"].value = start.toISOString().slice(0,16);
        document.forms[form]["end"].value = end.toISOString().slice(0,16);
        document.forms[form]["expires"].value = expires.toISOString().slice(0,16);
    }

    function OnSubmit(form) {
        //disable fixed settings in this mode
        document.forms[form]["heatmap"].disabled = false;
        document.forms[form]["live"].disabled = false;
        document.forms[form]["end"].disabled = false;
        document.forms[form]["start"].disabled = false;

        //adjust for timezone
        var start = new Date(document.forms[form]["start"].value);
        var end = new Date(document.forms[form]["end"].value);
        var expires = new Date(document.forms[form]["expires"].value);
        document.forms[form]["start"].value = start.toISOString().slice(0,16);
        document.forms[form]["end"].value = end.toISOString().slice(0,16);
        document.forms[form]["expires"].value = expires.toISOString().slice(0,16);

        return true;
    }

    modeUpdate("newShare");