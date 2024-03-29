import { Injectable } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {FormControl, FormGroup, Validators} from '@angular/forms';
import {Subject, throwError} from 'rxjs';
import {catchError, tap} from 'rxjs/operators';
import {formatDate} from '@angular/common';
import {Student} from '../models/student.model';
import {GlobalVariable} from '../shared/GlobalVariable';

@Injectable({
  providedIn: 'root'
})
export class FeesService {
  feesEntryForm: FormGroup;
  currentDate = new Date();
  feesDate =  formatDate(this.currentDate , 'dd/MM/yyyy', 'en');
  // feesDate =  formatDate('5/13/2021' , 'dd/MM/yyyy', 'en');
  dueByStudentData: any;
  courseByStudent: any;
  dueByStudentDataSub = new Subject<any>();
  billInfoData: any[] = [];
  billdataSub = new Subject<any>();



  billdataSubUpdateListener(){
    return this.billdataSub.asObservable();
  }


  dueByStudentDataUpdateListener(){
    return this.dueByStudentDataSub.asObservable();
  }


  constructor(private  http: HttpClient) {
    this.feesEntryForm = new FormGroup({
        id: new FormControl(null),
        student_name: new FormControl(null),
        fees: new FormControl(null, [Validators.required]),
        date: new FormControl(this.feesDate)
      // student_name: new FormControl(null),
      // fees: new FormControl(null),
      // date: new FormControl(null)
        }
    );

  }

  submitFees(data){
    this.feesEntryForm.value.date = formatDate(this.currentDate, 'yyyy-MM-dd', 'en');
    // this.feesEntryForm.value.date = formatDate('5/13/2021', 'yyyy-MM-dd', 'en');
    console.log(this.feesEntryForm.value);
    // return this.http.post('http://127.0.0.1:8000/api/saveFees', {courseInfo: data , formInfo: this.feesEntryForm.value}).pipe(catchError(this._serverError));
    return this.http.post(GlobalVariable.API_URL + 'saveFees', {courseInfo: data , formInfo: this.feesEntryForm.value}).pipe(catchError(this._serverError));
  }

  getCourseByStudent(id){
    // return  this.http.get('http://127.0.0.1:8000/api/getCourseByStudent/' + id);
    return  this.http.get(GlobalVariable.API_URL + 'getCourseByStudent/' + id);
  }

  viewDueFees(data1 , data2){
    return this.http.post(GlobalVariable.API_URL + 'dueFees', {studentId: data1, courseId: data2}).pipe(tap((response: {success: number, data1: any, data2: any})  => {
      this.dueByStudentData = response.data1;
      this.dueByStudentDataSub.next([...this.dueByStudentData]);
    }));
  }

  getBillInfo(){
    // console.log('data');
    this.feesEntryForm.value.date = formatDate(this.currentDate , 'yyyy/MM/dd', 'en');
    // this.feesEntryForm.value.date = formatDate('5/13/2021' , 'yyyy/MM/dd', 'en');
    console.log(this.feesEntryForm.value);
    return this.http.post(GlobalVariable.API_URL + 'getBillInfo', this.feesEntryForm.value)
      .pipe(tap( (response: {success: number, data: any}) => {
       if (response.data){
         this.billInfoData = response.data;
         this.billdataSub.next([...this.billInfoData]);
       }
   }));
  }

  setDiscount(data1, data2, data3){
   return  this.http.post(GlobalVariable.API_URL + 'setDiscount', {studentId: data1 , courseId: data2 , discount: data3})
     .pipe(catchError(this._serverError),(tap((response: {success: number , data: any}) => {
     })));
  }

  generateBill(billData){
    return this.http.post(GlobalVariable.API_URL + 'saveBill', {billInfo: billData});
  }
  getBillInfoData(){
    return [...this.billInfoData];
}

  private _serverError(err: any) {
    if (err instanceof Response) {
      return throwError('backend server error');
      // if you're using lite-server, use the following line
      // instead of the line above:
      // return Observable.throw(err.text() || 'backend server error');
    }
    if (err.status === 0){
      // tslint:disable-next-line:label-position
      return throwError ({status: err.status, message: 'Backend Server is not Working', statusText: err.statusText});
    }
    if (err.status === 401){
      // tslint:disable-next-line:label-position
      return throwError ({status: err.status, message: 'Your are not authorised', statusText: err.statusText});
    }
    if (err.status === 500){
      // tslint:disable-next-line:label-position
      return throwError ({status: err.status, message: 'Duplicate Entry', statusText: err.statusText});
    }
    return throwError(err);
  }
}
