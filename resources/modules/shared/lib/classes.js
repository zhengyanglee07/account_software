// eslint-disable-next-line import/prefer-default-export
export class Errors {
    constructor() {
        this.errors = {};
    }

    get(field) {
        if (this.errors[field]) {
            return this.errors[field][0];
        }
        return null;
    }

    has(field) {
        // if(this.errors )
        return Object.prototype.hasOwnProperty.call(this.errors, field);
    }

    record(errors) {
        this.errors = errors;
    }

    clear(field) {
        // console.log()
        // console.log(field);
        if (field) {
            delete this.errors[field];
        } else {
            this.errors = {};
        }
    }
}
